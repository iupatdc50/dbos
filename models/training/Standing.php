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
    public function wageShouldBe()
    {
        $sql = <<<SQL
  SELECT 
      H.total_hours,
      CMC.wage_percent,
      AP. wage_pct AS should_be
    FROM (SELECT TS.member_id, SUM(TS.total_hours) AS total_hours
            FROM Timesheets AS TS
            GROUP BY TS.member_id) AS H
      JOIN CurrentMemberClasses AS CMC ON CMC.member_id = H.member_id
      JOIN CurrentMemberStatuses AS CMS ON CMS.member_id = H.member_id
      JOIN ApprenticePercentages AS AP ON AP.lob_cd = CMS.lob_cd
                                        AND H.total_hours BETWEEN AP.min_hours AND AP.max_hours
    WHERE H.member_id = :member_id
;
SQL;

        $cmd = Yii::$app->db->createCommand($sql);
        $cmd->bindValues([
            ':member_id' => $this->member->member_id,
        ]);

        return $cmd->queryOne();

    }
}