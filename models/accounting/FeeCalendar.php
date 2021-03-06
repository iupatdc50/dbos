<?php

namespace app\models\accounting;

use app\components\utilities\OpDate;
use app\models\value\Lob;
use Yii;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "FeeCalendar".
 *
 * @property string $lob_cd
 * @property string $rate_class
 * @property string $end_dt
 * @property float $rate
 *
 * @property Lob $lob
 */
class FeeCalendar extends ActiveRecord
{
    const TOKEN_REFRESH = 'FeeCalRefreshReq';
    const TOKEN_REFRESH_DATA = '** Fee calendar refresh needed.';
    const REFRESH_YEARS = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FeeCalendar';
    }

    /**
     * @param $lob_cd
     * @param $rate_class
     * @param $dues_paid_thru_dt
     * @param null $months
     * @param OpDate|null $target_dt    Overrides today's date with a target date which, when combined with
     *                                  the $dues_paid_thru_dt, results in date range for the query
     * @return mixed
     */
    public static function getTrueDuesBalance($lob_cd, $rate_class, $dues_paid_thru_dt, $months = null, OpDate $target_dt = null)
    {
        $pt_dt = (new OpDate)->setFromMySql($dues_paid_thru_dt);
        $end_dt = (isset($target_dt)) ? $target_dt : new OpDate();
        $end_dt->setToMonthEnd();
        if (isset($months)) {
            $adj_end_dt = clone $pt_dt;
            $adj_end_dt->modify(OpDate::OP_ADD . $months . ' month');
            $adj_end_dt->setToMonthEnd();
            if (OpDate::dateDiff($adj_end_dt, $end_dt) > 0)
                $end_dt = $adj_end_dt;
        }
        $pt_dt->modify(OpDate::OP_ADD . '1 day');
        return self::find()->where(['lob_cd' => $lob_cd, 'rate_class' => $rate_class])
            ->andWhere(['between', 'end_dt', $pt_dt->getMySqlDate(), $end_dt->getMySqlDate()])
            ->sum('rate');
    }

    /**
     * Recursively calls FeeCalendar periods to compute periods covered
     *
     * @param $lob_cd
     * @param $rate_class
     * @param $start_dt
     * @param float $amt
     * @param int $periods
     * @return array Is in format ['periods' => (int), 'overage' => (float)]
     * @throws Exception
     */
    public static function getPeriodsCovered($lob_cd, $rate_class, $start_dt, $amt, $periods = 0)
    {
        if (!isset($lob_cd) || !isset($rate_class) || !isset($start_dt))
            throw new InvalidCallException("Missing required parameter(s). lob_cd: {$lob_cd}, rate_class: {$rate_class}, start_dt: {$start_dt} ");
        $qry = <<<SQL
            SELECT MIN(end_dt) AS end_dt, rate
              FROM FeeCalendar
              WHERE end_dt > :start_dt
                AND lob_cd = :lob_cd
                AND rate_class = :rate_class
            ;
SQL;
        $db = yii::$app->db;
        $cmd = $db->createCommand($qry)
            ->bindValues([
                ':lob_cd' => $lob_cd,
                ':rate_class' => $rate_class,
                ':start_dt' => $start_dt,
            ]);
        $result = $cmd->queryOne();

        if ((float)$result['rate'] <= $amt)
            // Can't use standard substract on FP numbers
            return self::getPeriodsCovered($lob_cd, $rate_class, $result['end_dt'], (float)bcsub($amt, (float)$result['rate'], 2), $periods + 1);
        return ['periods' => $periods, 'overage' => $amt];
    }

    /**
     * @param int $years
     */
    public static function tableRefresh($years = 5)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lob_cd', 'rate_class', 'end_dt', 'rate'], 'required'],
            [['end_dt'], 'safe'],
            [['rate'], 'number'],
            [['lob_cd'], 'string', 'max' => 4],
            [['rate_class'], 'string', 'max' => 2],
            [['lob_cd', 'rate_class', 'end_dt'], 'unique', 'targetAttribute' => ['lob_cd', 'rate_class', 'end_dt']],
            [['lob_cd'], 'exist', 'skipOnError' => true, 'targetClass' => Lob::className(), 'targetAttribute' => ['lob_cd' => 'lob_cd']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'lob_cd' => 'Lob Cd',
            'rate_class' => 'Rate Class',
            'end_dt' => 'End Dt',
            'rate' => 'Rate',
        ];
    }

    /**
     * Gets query for [[LobCd]].
     *
     * @return ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

}
