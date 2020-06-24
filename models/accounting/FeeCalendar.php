<?php

namespace app\models\accounting;

use app\components\utilities\OpDate;
use app\models\value\Lob;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
     * @param OpDate|null $today
     * @return mixed
     */
    public static function getTrueDuesBalance($lob_cd, $rate_class, $dues_paid_thru_dt, $months = null, OpDate $today = null)
    {
        $pt_dt = (new OpDate)->setFromMySql($dues_paid_thru_dt);
        $end_dt = (isset($today)) ? $today : new OpDate();
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
