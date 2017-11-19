<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\accounting\DuesRateFinder;
use app\models\member\Member;
use app\models\member\Standing;
use app\models\contractor\Contractor;
use app\modules\admin\models\FeeType;

class StagedBill extends \yii\db\ActiveRecord
{
	/**
	 * @var Standing 	May be injected, if required
	 */
	private $_standing;
	/**
	 * @var RateFinder 	May be injected, if required
	 */
	private $_rateFinder;
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'StagedBills';
	}
	
	public function attributes()
	{
		return array_merge(parent::attributes(), ['PC', 'JT', 'LM', 'IU']);
	}
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getMember()
	{
		return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
	}
	
	public function getStanding()
	{
		if(!(isset($this->_standing)))
			$this->_standing = new Standing(['member' => $this->member]);
		return $this->_standing;
	}
	
	public function setStanding(Standing $standing)
	{
		$this->_standing = $standing;
	}
	
	public function getRateFinder()
	{
		if(!(isset($this->_rateFinder))) {
			$member = $this->member;
			$lob_cd = $member->currentStatus->lob_cd;
			$rate_class = $member->currentClass->rate_class;
			$this->_rateFinder = new DuesRateFinder($lob_cd, $rate_class);
		}
		return $this->_rateFinder;
	}
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getEmployer()
	{
		return $this->hasOne(Contractor::className(), ['license_nbr' => 'dues_payor']);
	}
	
	/**
	 * Determines dues amount employer can collect for this member
	 * 
	 * @return number
	 */
	public function getDU()
	{
		if ($this->employer->deducts_dues == 'T')
			return $this->standing->getDuesBalance($this->rateFinder);
		return null;
	}
	
	/**
	 * Determines APF amount employer can collect for this member
	 * 
	 * @return number
	 */
	public function getIN()
	{
		if ($this->employer->deducts_dues == 'T')
			return $this->standing->getOutstandingAssessment(FeeType::TYPE_INIT);
		return null;
	}
	
	/**
	 * Returns data provider for bill line item with pre-filled formulae fr spreadsheet columns
	 *   
	 * @param unknown $license_nbr
	 * @param unknown $lob_cd
	 * @return \yii\data\ActiveDataProvider
	 */
	public function getPreFill($license_nbr, $lob_cd)
	{
		// @row_number counter starts at 1 to hold a place for the header, i.e., first data row is 2 on the spreadsheet 
		$sql = "
			SELECT 
                dues_payor, member_id,
				classification, last_nm, first_nm, middle_inits, report_id, member_status,
			    CASE WHEN pc_operand IS NULL THEN pc_factor ELSE CONCAT('=', pc_operand, seq, '*', pc_factor) END AS PC,
			    CASE WHEN jt_operand IS NULL THEN jt_factor ELSE CONCAT('=', jt_operand, seq, '*', jt_factor) END AS JT,
			    CASE WHEN lm_operand IS NULL THEN lm_factor ELSE CONCAT('=', lm_operand, seq, '*', lm_factor) END AS LM,
				CASE WHEN iu_operand IS NULL THEN iu_factor ELSE CONCAT('=', iu_operand, seq, '*', iu_factor) END AS IU
			  FROM (				
				SELECT 
					(@row_number:=@row_number + 1) AS seq,
					classification, last_nm, first_nm, middle_inits, report_id, member_status,
                    dues_payor, member_id,
					pc_factor, pc_operand,
				    jt_factor, jt_operand,
				    lm_factor, lm_operand,
				    iu_factor, iu_operand
				  FROM (
				    SELECT 
				        classification, last_nm, first_nm, middle_inits, report_id, member_status
                       ,dues_payor
                       ,member_id
	                   ,lob_cd
                       ,pc_factor, pc_operand
                       ,jt_factor, jt_operand
                       ,lm_factor, lm_operand
                       ,iu_factor, iu_operand
				      FROM StagedBills
				      WHERE dues_payor = :license_nbr
				        AND lob_cd = :lob_cd
				    ORDER BY classification, last_nm, first_nm, middle_inits
				  ) AS b, (SELECT @row_number:=1) AS t
				  		
				) AS f
				";
		
		$query = StagedBill::findBySql($sql, [':license_nbr' => $license_nbr, ':lob_cd' => $lob_cd]);
		
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => ['pageSize' => 2000],
		]);
		
		return $dataProvider;
	} 
	
}

