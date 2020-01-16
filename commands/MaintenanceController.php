<?php

namespace app\commands;

use app\models\project\jtp\Project;
use app\models\user\User;
use Yii;
use yii\console\Controller;
use app\components\utilities\OpDate;
use app\models\member\Member;
use app\models\member\Status;
use app\models\accounting\AdminFee;
use app\modules\admin\models\FeeType;
use yii\db\Command;
use yii\db\Connection;
use yii\db\Exception;

class MaintenanceController extends Controller
{
    // Prefix for user specific receipt processing
    CONST PFX = 'StagedAllocations';

	CONST STAGE_TABLE_NM = 'StageStatusCandidates';
	CONST CLOSE_PREV_TABLE_NM = 'StageClosePrevious';
	CONST STAGE_CXL_PROJ_TABLE_NM = 'StageCancelProjects';
	
	CONST IX_CUTOFF = 'cutoff';
	CONST IX_EFFECTIVE = 'effective';

	/** @var Connection */
	public $db;
    /** @var Command */
	public $drop_stage_cmd;
    /** @var Command */
	public $drop_close_prev_cmd;
    /** @var Command */
	public $drop_cxl_projects_cmd;

	private $stageTableNm;
	private $closePrevTableNm;
	private $stageCxlProjTableNm;
		
	public function init()
	{
		$this->db = Yii::$app->db;
		$this->drop_stage_cmd = $this->db->createCommand('DROP TABLE IF EXISTS ' . self::CLOSE_PREV_TABLE_NM);
		$this->drop_close_prev_cmd = $this->db->createCommand('DROP TABLE IF EXISTS ' . self::STAGE_TABLE_NM);
        $this->drop_cxl_projects_cmd = $this->db->createCommand('DROP TABLE IF EXISTS ' . self::STAGE_CXL_PROJ_TABLE_NM);

        $this->stageTableNm = self::STAGE_TABLE_NM;
        $this->closePrevTableNm = self::CLOSE_PREV_TABLE_NM;
        $this->stageCxlProjTableNm = self::STAGE_CXL_PROJ_TABLE_NM;

	}

    /**
     * @param null $today
     * @throws Exception
     * @noinspection DuplicatedCode
     */
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
			Yii::info(self::STAGE_TABLE_NM . " table generated");
			$problems = $this->db->createCommand($this->problemMemberSql(Member::MONTHS_DELINQUENT))->queryAll();
			if (!empty($problems))
			    Yii::warning('*** Members bypassed: ' . print_r($problems, true));
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
		} catch (Exception $e) {
			Yii::error('*** MC020 Problem with a suspend DB action. Messages: ' . print_r($e->getMessage(), true));
		}
					
	}

    /**
     * @param null $today
     * @throws Exception
     * @noinspection DuplicatedCode
     */
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
			Yii::info(self::STAGE_TABLE_NM . " table generated");
            $problems = $this->db->createCommand($this->problemMemberSql(Member::MONTHS_GRACE_PERIOD))->queryAll();
            if (!empty($problems))
                Yii::warning('*** Members bypassed: ' . print_r($problems, true));
			$count = $this->db->createCommand($this->insertStatusSql())->execute();
			Yii::info("Members dropped as of {$dates[self::IX_EFFECTIVE]->getDisplayDate()}: {$count}");
			$this->db->createCommand($this->stagePrevCloseSql())->execute();
			Yii::info(self::CLOSE_PREV_TABLE_NM . "table generated");
			$count = $this->db->createCommand($this->updateStatusSql())->execute();
			Yii::info("Previous status entries closed: {$count}");

			$count = $this->db->createCommand($this->closeEmploymentSql())->execute();
			Yii::info("Employment records closed: {$count}");
		
			$this->cleanupTemps();
			Yii::info('Monthly drop run completed');
		} catch (Exception $e) {
			Yii::error('*** MC020 Problem with a drop DB action. Messages: ' . print_r($e->getMessage(), true));
		}
			
		
	}

    /**
     * @param null $today
     * @throws Exception
     */
	public function actionCancelJtp($today = null)
    {
        Yii::info('Starting monthly cancel non-awarded JTP run');
        $today_obj = new OpDate();
        if (isset($today))
            $today_obj->setFromMySql($today);

        // Just in case
        $this->drop_cxl_projects_cmd->execute();

        try {

            $this->db->createCommand($this->stageCancelProjectSql())
                ->bindValues([
                    ':months' => Project::MONTHS_TO_AWARD,
                    ':dt' => $today_obj->getMySqlDate(),
                ])
                ->execute();
            Yii::info(self::STAGE_CXL_PROJ_TABLE_NM . " table generated");
            $count = $this->db->createCommand($this->cancelOldProjectsSql())
                ->bindValues([
                    ':dt'  => $today_obj->getMySqlDate(),
                ])
                ->execute();
            Yii::info("JTP projects cancelled as of {$today_obj->getDisplayDate()}: {$count}");
            $count = $this->db->createCommand($this->cancelProjectNotesSql())
                ->bindValues([
                    ':dt'  => $today_obj->getMySqlDate(),
                    ':months' => Project::MONTHS_TO_AWARD,
                ])
                ->execute();
            Yii::info("JTP projects notes generated: {$count}");
            $this->drop_cxl_projects_cmd->execute();

        }  catch (Exception $e) {
            Yii::error('*** MC030 Problem with a cancel project DB action. Messages: ' . print_r($e->getMessage(), true));
        }
    }

    public function actionCleanup()
    {
        Yii::info('Starting StageAllocations removal');
        /* @var User $user */
        $user = User::find()->orderBy(['id' => SORT_DESC])->limit(1)->one();
        $hits = 0;
        for ($x = 1; $x <= $user->id; $x++) {
            try {
                $table_nm = self::PFX . $x;
                $hits += $this->db->createCommand("SHOW TABLES LIKE '{$table_nm}'")->execute();
                $this->db->createCommand('DROP TABLE IF EXISTS ' . $table_nm)->execute();
            } catch (Exception $e) {
                Yii::error("*** MC040 Problem with drop {$table_nm} DB action. Messages: " . print_r($e->getMessage(), true));
            }
        }
        Yii::info("StagedAllocation tables dropped: {$hits}");
    }

    /**
     * @throws Exception
     */
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
		return <<<SQL
              CREATE TEMPORARY TABLE $this->stageTableNm AS 
			      SELECT 
			          Me.member_id, 
			          DATE_ADD(dues_paid_thru_dt, INTERVAL 1 DAY) + INTERVAL $months MONTH - INTERVAL 1 DAY AS effective_dt,  
			          MS.lob_cd, 
			          '$new_status' AS member_status, 
			          '$reason' AS reason   
			        FROM Members AS Me 
			          JOIN MemberStatuses AS MS ON MS.member_id = Me.member_id 
			                                     AND MS.member_status = '$status' 
			                                     AND MS.end_dt IS NULL 
                    WHERE dues_paid_thru_dt <= :cutoff_dt 
			          AND lob_cd <> '1941'
			  ;
SQL;

	}

	private function problemMemberSql($months)
    {
        return <<<SQL
    # noinspection SqlResolve
    SELECT 
        SSC.member_id, 
        Me.last_nm, Me.first_nm, Me.middle_inits, Me.suffix, 
        SSC.effective_dt AS action_dt, 
        CMS.effective_dt AS latest_entry 
      FROM  $this->stageTableNm  AS SSC 
        JOIN Members AS Me ON Me.member_id = SSC.member_id 
          JOIN CurrentMemberStatuses AS CMS ON CMS.member_id = Me.member_id 
                                             AND DATE_ADD(Me.dues_paid_thru_dt, INTERVAL 1 DAY) + INTERVAL $months MONTH - INTERVAL 1 DAY <= CMS.effective_dt 
;
SQL;



    }
	
	private function insertStatusSql()
	{
       return <<<SQL
    # noinspection SqlResolve
    INSERT INTO MemberStatuses (member_id, effective_dt, lob_cd, member_status, reason) 
      SELECT DISTINCT member_id, effective_dt, lob_cd, member_status, reason 
        FROM $this->stageTableNm AS MSC
        WHERE NOT EXISTS 
             (SELECT 1 FROM CurrentMemberStatuses 
                WHERE member_id = MSC.member_id 
                  AND MSC.effective_dt <= effective_dt)
    ;
SQL;

	}
	
	private function insertAssessSql()
	{
	    $type = FeeType::TYPE_REINST;
		return <<<SQL
    # noinspection SqlResolve
    INSERT INTO Assessments (member_id, fee_type, assessment_dt, assessment_amt, purpose, created_at, created_by, months) 
         SELECT distinct MSC.member_id, '$type', MSC.effective_dt, :assess_amt, 'Suspended on this date', :run_stamp, 1, 0 
           FROM $this->stageTableNm AS MSC 
             JOIN  CurrentMemberStatuses AS CMS ON CMS.member_id = MSC.member_id 
                                                 AND CMS.effective_dt = MSC.effective_dt
    ; 

SQL;


		
	}
	
	private function stagePrevCloseSql()
	{
		return <<<SQL
    CREATE TABLE $this->closePrevTableNm AS 
        SELECT 
            (@row_number:=@row_number + 1) AS stat_nbr, 
            MS.member_id, 
            MS.effective_dt 
          FROM MemberStatuses AS MS, (SELECT @row_number:=0) AS t 
          WHERE MS.end_dt IS NULL 
            AND MS.member_id IN (SELECT DISTINCT member_id FROM $this->stageTableNm) 
        ORDER BY MS.member_id, MS.effective_dt
    ;

SQL;

	}
	
	private function updateStatusSql()
	{
		return <<<SQL
    # noinspection SqlResolve
    UPDATE MemberStatuses AS MS 
	  JOIN $this->closePrevTableNm AS B ON MS.member_id = B.member_id AND MS.effective_dt = B.effective_dt 
	    LEFT OUTER JOIN $this->closePrevTableNm AS N ON N.member_id = B.member_id AND N.stat_nbr = B.stat_nbr + 1 
	  SET MS.end_dt = DATE_ADD(N.effective_dt, INTERVAL -1 DAY) 
	  WHERE N.effective_dt IS NOT NULL
    ; 

SQL;

	}

	private function closeEmploymentSql() {

        return <<<SQL
    UPDATE Employment AS Em
      JOIN CurrentMemberStatuses AS CMS ON CMS.member_id = Em.member_id
                                         AND CMS.member_status = 'I'
      SET Em.end_dt = CMS.effective_dt, term_reason = 'M'
      WHERE Em.end_dt IS NULL
    ;
SQL;

    }

    private function stageCancelProjectSql()
    {
        return <<<SQL
    CREATE TEMPORARY TABLE $this->stageCxlProjTableNm AS 
        SELECT Pr.project_id 
          FROM Projects AS Pr
            JOIN Registrations AS Re ON Re.project_id = Pr.project_id 
                                      AND Re.`id` = (SELECT MIN(`id`) FROM Registrations WHERE project_id = Pr.project_id)
          WHERE Pr.agreement_type = 'JTP'
            AND Pr.disposition = 'A'
            AND Pr.project_status = 'A'
            AND Pr.project_id NOT IN (SELECT project_id FROM AwardedBids)
            AND DATE_ADD(Re.bid_dt, INTERVAL :months MONTH) <= :dt 
          ;

SQL;

    }

    private function cancelOldProjectsSql()
    {
        return <<<SQL
    UPDATE Projects 
      SET project_status = 'X', close_dt = :dt
      WHERE project_id IN (SELECT project_id FROM $this->stageCxlProjTableNm)
    ;
SQL;

    }

    private function cancelProjectNotesSql()
    {
        return <<<SQL
    # noinspection SqlResolve
    INSERT INTO ProjectNotes (project_id, note, created_at, created_by)
      SELECT project_id, CONCAT('[CANCELLED ', :dt, ']: Unawarded after ', :months, ' months'), UNIX_TIMESTAMP(), 1
        FROM $this->stageCxlProjTableNm
    ;
SQL;

    }

}
