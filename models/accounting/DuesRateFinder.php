<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use app\components\utilities\OpDate;

class DuesRateFinder extends Model
{
	/*
	 * Acts as a subquery for functions in this finder.  All selections are specific to :lob_cd and :rate_class.
	 * Given a :start_dt and :target_dt, all dues rate entries are selected.  The left_dt will be :start_dt
	 * for the earliest entry selected and DuesRate.effective_dt for all others.  The right_dt will be :target_dt 
	 * for the latest entry selected and DuesRate.end_dt for all others. 
	 */
	const INTERVAL_QRY = 
		"SELECT
			CASE WHEN :start_dt >= DR.effective_dt THEN :start_dt ELSE DR.effective_dt END AS left_dt
			,CASE WHEN :target_dt <= COALESCE(DR.end_dt, :target_dt) THEN :target_dt ELSE DR.end_dt END AS right_dt
			,rate
		  FROM DuesRates AS DR
		  WHERE DR.lob_cd = :lob_cd AND DR.rate_class = :rate_class
			AND (
					(DR.effective_dt <= :start_dt AND DR.end_dt >= :start_dt)
					OR (DR.effective_dt >= :start_dt AND DR.end_dt <= :target_dt)
					OR (DR.effective_dt <= :target_dt AND COALESCE(DR.end_dt, :target_dt) >= :target_dt)
			)";
	
	private $_lob_cd;
	private $_rate_class;
	
	/**
	 * Force required trade and rate class input
	 * 
	 * @param string $lob_cd
	 * @param string $rate_class
	 * @param array $config
	 * @throws \yii\base\InvalidConfigException
	 */
	public function __construct($lob_cd, $rate_class, $config = [])
	{
		$this->_lob_cd = $lob_cd;
		$this->_rate_class = $rate_class;
		parent::__construct($config = []);
	}
	
	/**
	 * Use DuesRate table to compute total dues balance for a given date range 
	 * 
	 * @param string $start_dt 
	 * @param string $target_dt
	 * @returns decimal
	 */
	public function computeBalance($start_dt, $target_dt)
	{
		$sql = 
			"SELECT 
     			SUM(rate * period_diff(date_format(right_dt, '%Y%m'), date_format(date_add(left_dt, INTERVAL -1 DAY), '%Y%m'))) AS dues_balance
			  FROM (" . self::INTERVAL_QRY . ") AS Ab;";
		$db = yii::$app->db;
		$cmd = $db->createCommand($sql)
				   ->bindValues([
				   		':lob_cd' => $this->_lob_cd, 
				   		':rate_class' => $this->_rate_class, 
				   		':start_dt' => $start_dt, 
				   		':target_dt' => $target_dt
				   ]);
		return $cmd->queryScalar();
	}

	public function getRatePeriods($start_dt)
	{
		$target_dt = (new OpDate)->setFromMySql($start_dt);
		// Assume no one pays more than 5 years of dues at a time
		$target_dt->modify('+5 year');
		$sql =
			"SELECT
				left_dt
			   ,right_dt
			   ,rate
			   ,period_diff(date_format(right_dt, '%Y%m'), date_format(date_add(left_dt, INTERVAL -1 DAY), '%Y%m')) AS months_in_period
     		   ,rate * period_diff(date_format(right_dt, '%Y%m'), date_format(date_add(left_dt, INTERVAL -1 DAY), '%Y%m')) AS max_in_period
			  FROM (" . self::INTERVAL_QRY . ") AS RP ORDER BY left_dt;";
		$db = yii::$app->db;
		$cmd = $db->createCommand($sql)
				  ->bindValues([
							':lob_cd' => $this->_lob_cd,
							':rate_class' => $this->_rate_class,
							':start_dt' => $start_dt,
							':target_dt' => $target_dt->getMySqlDate(),
				  ]);
		return $cmd->query();
	}
	
}