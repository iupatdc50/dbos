<?php

namespace app\models\accounting;

use app\modules\admin\models\FeeType;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "AdminFees".
 *
 * @property string $fee_type
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $fee
 *
 * @property FeeType $feeType
 */
class AdminFee extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AdminFees';
    }

    /**
     * Return any preset admin fee that matches the fee type
     *
     * @param string $fee_type
     * @param string $date MySQL format
     * @return false|string|null
     */
    public static function getFee($fee_type, $date)
    {
        return self::find()->select('fee')
                           ->where(['fee_type' => $fee_type])
                           ->andWhere(['<=', 'effective_dt', $date])
                           ->andWhere(['or', ['end_dt' => null], ['>=', 'end_dt', $date]])
                           ->scalar()
        ;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fee_type', 'effective_dt', 'fee'], 'required'],
            [['effective_dt', 'end_dt'], 'safe'],
            [['fee'], 'number'],
            [['fee_type'], 'string', 'max' => 2],
            [['fee_type'], 'exist', 'skipOnError' => true, 'targetClass' => FeeType::className(), 'targetAttribute' => ['fee_type' => 'fee_type']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fee_type' => 'Fee Type',
            'effective_dt' => 'Effective Dt',
            'end_dt' => 'End Dt',
            'fee' => 'Fee',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
    }
}
