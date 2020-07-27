<?php


namespace app\models\training;


use app\models\member\Member;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Exception;

/**
 * Model class for determining the wage status of a member
 *
 * Requires the injection of a Member
 */
class Standing extends Model
{

    /**
     * @var Member
     */
    public $member;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if(!(isset($this->member) && ($this->member instanceof Member)))
            throw new InvalidConfigException('No member object injected');
    }

    /**
     * @return array|false
     * @throws Exception
     */
    public function ojt()
    {
        /** @noinspection SqlCaseVsIf */
        /** @noinspection SqlResolve */
        $sql = <<<SQL
    SELECT
        CASE WHEN MCI.lob_cd = '1941' THEN 'Industrial' ELSE SUBSTR(MCI.trade, 7, LENGTH(MCI.trade) - 7) END AS trade, 
        MCI.full_nm, CONCAT(MCI.wage_pct, '% wage rate') AS wage_rate,
        Me.imse_id AS iupat_id, Me.init_dt AS indenture_dt,
        CMS.effective_dt AS clearance_requested,
        CASE 
          WHEN MCI.class = 'Apprentice' THEN CONCAT('Step ', AP.max_hours DIV 1000, ' ', MCI.class) 
          WHEN MCI.class = 'Material Handler' THEN CONCAT('Step ', MH.max_hours DIV 1000, ' ', MCI.class) 
          ELSE MCI.class
        END AS classification,
        CASE WHEN MCI.class IN ('Apprentice', 'Material Handler') THEN OJT.hours END AS hours
      FROM Members AS Me
        JOIN MemberContactInfo AS MCI ON MCI.member_id = Me.member_id
          LEFT OUTER JOIN ApprenticePercentages AS AP ON AP.lob_cd = MCI.lob_cd
                                                       AND AP.wage_pct = MCI.wage_pct
          LEFT OUTER JOIN MHPercentages AS MH ON MH.lob_cd = MCI.lob_cd
                                               AND MH.wage_pct = MCI.wage_pct
        LEFT OUTER JOIN CurrentMemberStatuses AS CMS ON CMS.member_id = Me.member_id
                                                      AND (CMS.reason LIKE '%CCG%' OR CMS.reason LIKE '%CC granted%')
        LEFT OUTER JOIN (SELECT member_id, SUM(total_hours) AS hours FROM Timesheets GROUP BY member_id) AS OJT
                     ON OJT.member_id = Me.member_id
      WHERE Me.member_id = :member_id
      ;

SQL;
        return $this->runSql($sql);

    }

    /**
     * @param $sql
     * @return array|false
     * @throws Exception
     */
    private function runSql($sql)
    {
        $cmd = Yii::$app->db->createCommand($sql);
        $cmd->bindValues([
            ':member_id' => $this->member->member_id,
        ]);

        return $cmd->queryOne();
    }
}