<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\base\InvalidParamException;
use app\components\utilities\OpDate;
use app\models\member\Member;
use app\models\member\Status;
use app\models\accounting\AdminFee;
use app\modules\admin\models\FeeType;

class MaintenanceController extends Controller
{
	CONST STAGE_TABLE_NM = 'StagedStatusCandidates';
	CONST CLOSE_PREV_TABLE_NM = 'StageClosePrevious';
	
	CONST IX_CUTOFF = 'cutoff';
	CONST IX_EFFECTIVE = 'effective';
	
	public $db;
	public $drop_stage_cmd;
	public $drop_close_prev_cmd;
		
	public function init()
	{
		$this->db = Yii::$app->db;
		$this->drop_stage_cmd = $this->db->createCommand('DROP TABLE IF EXISTS ' . self::CLOSE_PREV_TABLE_NM);
		$this->drop_close_prev_cmd = $this->db->createCommand('DROP TABLE IF EXISTS ' . self::STAGE_TABLE_NM);

	}
	
	public function actionSuspend($today = null)
	{
		Yii::info('Starting monthly suspend run');
		$dates = $this->getGracePeriod(Member::MONTHS_DELINQUENT, $today);

		
		// Just in case
		$this->cleanupTemps(); 
		
		try {
			$this->db->createCommand($this->stageStatusSql(Member::MONTHS_DELINQUENT, Status::ACTIVE, Status::SUSPENDED))
			   		 ->bindValues([
			         		':cutoff_dt' => $dates[self::IX_CUTOFF]->getMySqlDate(),
			   		 ])
			   		 ->execute();	
			Yii::info(self::STAGE_TABLE_NM . "table generated");
			$count = $this->db->createCommand($this->insertStatusSql())->execute();
			Yii::info("Members suspended as of {$dates[self::IX_EFFECTIVE]->getDisplayDate()}: {$count}");
			$this->db->createCommand($this->stagePrevCloseSql())->execute();
			Yii::info(self::CLOSE_PREV_TABLE_NM . "table generated");
			$count = $this->db->createCommand($this->updateStatusSql())->execute();
			Yii::info("Previous status entries closed: {$count}");
			$count = $this->db->createCommand($this->insertAssessSql())
			   				  ->bindValues([
			   							':assess_amt' => AdminFee::getFee(FeeType::TYPE_REINST, $dates[self::IX_EFFECTIVE]->getMySqlDate()), 
			   							':run_stamp' => time(),
			   				  ])->execute();
			Yii::info("Reinstatement assessments inserted: {$count}");
			   
			$this->cleanupTemps(); 
			Yii::info('Monthly suspend run completed');
		} catch (yii\db\Exception $e) {
			Yii::error('*** MC020 Problem with a suspend DB action. Messages: ' . print_r($e->getMessage(), true));
		}
					
	}
	
	public function actionDrop($today = null)
	{
		Yii::info('Starting monthly drop run');
		$dates = $this->getGracePeriod(Member::MONTHS_GRACE_PERIOD, $today);
		
		
		// Just in case
		$this->cleanupTemps();
		
		try {
			$this->db->createCommand($this->stageStatusSql(Member::MONTHS_GRACE_PERIOD, Status::SUSPENDED, Status::INACTIVE))
			         ->bindValues([
			         		':cutoff_dt' => $dates[self::IX_CUTOFF]->getMySqlDate(),
			         ])
			         ->execute();
			Yii::info(self::STAGE_TABLE_NM . "table generated");
			$count = $this->db->createCommand($this->insertStatusSql())->execute();
			Yii::info("Members dropped as of {$dates[self::IX_EFFECTIVE]->getDisplayDate()}: {$count}");
			$this->db->createCommand($this->stagePrevCloseSql())->execute();
			Yii::info(self::CLOSE_PREV_TABLE_NM . "table generated");
			$count = $this->db->createCommand($this->updateStatusSql())->execute();
			Yii::info("Previous status entries closed: {$count}");
		
			$this->cleanupTemps();
			Yii::info('Monthly drop run completed');
		} catch (yii\db\Exception $e) {
			Yii::error('*** MC020 Problem with a drop DB action. Messages: ' . print_r($e->getMessage(), true));
		}
			
		
	}
	
	private function cleanupTemps()
	{
		$this->drop_close_prev_cmd->execute();
		$this->drop_stage_cmd->execute();
	}
	
	private function getGracePeriod($months, $today = null)
	{
		$period = [];
		$effective_dt = new OpDate();
		if (isset ($today))
			$effective_dt->setFromMySql($today);
		$effective_dt->setToMonthBegin();
		$effective_dt->modify('-1 day');
		$period[self::IX_EFFECTIVE] = $effective_dt;
		
		$cutoff_dt = clone $effective_dt;
		$cutoff_dt->modify('-' . $months . ' month');
		$cutoff_dt->setToMonthEnd();
		$period[self::IX_CUTOFF] = $cutoff_dt;
		
		return $period;
	}

	private function stageStatusSql($months, $status, $new_status)
	{
		$reason = 'Dues over ' . $months . ' months delinquent';
		if ($new_status == Status::INACTIVE)
			$reason = 'Member dropped. ' . $reason; 
		return "  CREATE TEMPORARY TABLE " . self::STAGE_TABLE_NM . " AS "
			  ."    SELECT "
			  ."        Me.member_id, "
			  ."        DATE_ADD(dues_paid_thru_dt, INTERVAL 1 DAY) + INTERVAL {$months} MONTH - INTERVAL 1 DAY AS effective_dt,  "
			  ."        MS.lob_cd, "
			  ."        '{$new_status}' AS member_status, "
			  ."        '{$reason}' AS reason   "
			  ."      FROM Members AS Me "
			  ."        JOIN MemberStatuses AS MS ON MS.member_id = Me.member_id "
			  ."                                   AND MS.member_status = '{$status}' "
			  ."                                   AND MS.end_dt IS NULL "
		      ."  		LEFT OUTER JOIN Employment AS Em ON Em.member_id = Me.member_id "
		      ."                                    	  AND Em.end_dt IS NULL "
		      ."    	  LEFT OUTER JOIN Contractors AS Co ON Co.license_nbr = Em.dues_payor "
			  ."      WHERE dues_paid_thru_dt <= :cutoff_dt AND COALESCE(Co.deducts_dues, 'F') = 'F' "
			  ."        AND lob_cd <> '1941'"
			  .";"
		;
	}
	
	private function insertStatusSql()
	{
		return " INSERT INTO dc50.MemberStatuses (member_id, effective_dt, lob_cd, member_status, reason) "
			  ."   SELECT distinct member_id, effective_dt, lob_cd, member_status, reason "
			  ."     FROM " . self::STAGE_TABLE_NM . ";"
		;
		
	}
	
	private function insertAssessSql()
	{
		return " INSERT INTO dc50.Assessments (member_id, fee_type, assessment_dt, assessment_amt, purpose, created_at, created_by, months) "
			  ."   SELECT distinct member_id, '" . FeeType::TYPE_REINST . "', effective_dt, :assess_amt, 'Suspended on this date', :run_stamp, 1, 0 "
			  ."     FROM " . self::STAGE_TABLE_NM . ";"
		;
		
	}
	
	private function stagePrevCloseSql()
	{
		return "CREATE TABLE " . self::CLOSE_PREV_TABLE_NM . " AS " 
			  ."  SELECT "
			  ."      (@row_number:=@row_number + 1) AS stat_nbr, "
			  ."      MS.member_id, "
			  ."      MS.effective_dt "
			  ."    FROM dc50.MemberStatuses AS MS, (SELECT @row_number:=0) AS t "
			  ."    WHERE MS.end_dt IS NULL "
			  ."      AND MS.member_id IN (SELECT DISTINCT member_id FROM " . self::STAGE_TABLE_NM . ") "
			  ."  ORDER BY MS.member_id, MS.effective_dt;"
		;	
	}
	
	private function updateStatusSql()
	{
		return "UPDATE dc50.MemberStatuses AS MS "
			  ."  JOIN " . self::CLOSE_PREV_TABLE_NM . " AS B ON MS.member_id = B.member_id AND MS.effective_dt = B.effective_dt "
			  ."    LEFT OUTER JOIN " . self::CLOSE_PREV_TABLE_NM . " AS N ON N.member_id = B.member_id AND N.stat_nbr = B.stat_nbr + 1 "
			  ."  SET MS.end_dt = DATE_ADD(N.effective_dt, INTERVAL -1 DAY) "
		      ."  WHERE N.effective_dt IS NOT NULL; "
		;
	}
	
}
