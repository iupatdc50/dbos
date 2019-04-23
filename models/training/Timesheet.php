<?php

namespace app\models\training;

use app\helpers\OptionHelper;
use app\models\member\Member;
use app\models\user\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "Timesheets".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $acct_month
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Member $member
 * @property WorkHour[] $workHour
 * @property User $createdBy
 * @property string $enteredBy
 */
class Timesheet extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Timesheets';
    }

    /**
     * @param $processes  array Work processes that will become columns
     * @return string
     */
    public static function getFlattenedTimesheetsSql($processes)
    {
        $cols = '';
        foreach ($processes as $process)
            $cols .= "MAX(CASE WHEN WH.wp_seq = " . $process['seq'] . " THEN WH.hours ELSE NULL END) AS `" . $process['work_process'] . "`, ";

        $sql =

            "SELECT
                 T.`id`,
                 DATE_FORMAT(CONCAT(SUBSTRING(T.acct_month, 1, 4), '-', SUBSTRING(T.acct_month, 5, 2), '-01'), '%b %Y') AS acct_month, " .
            $cols .
            "    SUM(WH.hours) AS total,
                 U.username,
                 T.created_at
               FROM Timesheets AS T 
                 JOIN WorkHours AS WH ON T.`id` = WH.timesheet_id
                 JOIN Users AS U ON T.created_by = U.`id`
               WHERE T.member_id = :member_id
               GROUP BY T.`id`, T.acct_month
            ORDER BY T.acct_month DESC
            ";

        return $sql;
    }

    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::className()],
            ['class' => BlameableBehavior::className()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'acct_month', 'created_at', 'created_by'], 'required'],
            [['created_at', 'created_by'], 'integer'],
            [['member_id'], 'string', 'max' => 11],
            [['acct_month'], 'string', 'max' => 6],
            [['member_id', 'acct_month'], 'unique', 'targetAttribute' => ['member_id', 'acct_month'], 'message' => 'The combination of Member ID and Acct Month has already been taken.'],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'acct_month' => 'Acct Month',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getWorkHour()
    {
        return $this->hasMany(WorkHour::className(), ['timesheet_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getAcctMonthText()
    {
        return OptionHelper::getPrettyMonthYear($this->acct_month);
    }



    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getEnteredBy()
    {
        return $this->createdBy->username . ' on ' . date('m/d/Y h:i a', $this->created_at);
    }
}
