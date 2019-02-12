<?php

namespace app\models\member;

use app\models\accounting\Receipt;

/**
 * This is the model class for table "OverageHistory".
 *
 * @property string $member_id
 * @property string $dues_paid_thru_dt
 * @property integer $receipt_id
 * @property string $overage
 *
 * @property Member $member
 * @property Receipt $receipt
 */
class OverageHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'OverageHistory';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'dues_paid_thru_dt', 'receipt_id', 'overage'], 'required'],
            [['dues_paid_thru_dt'], 'safe'],
            [['receipt_id'], 'integer'],
            [['overage'], 'number'],
            [['member_id'], 'string', 'max' => 11],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
            [['receipt_id'], 'exist', 'skipOnError' => true, 'targetClass' => Receipt::className(), 'targetAttribute' => ['receipt_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'dues_paid_thru_dt' => 'Dues Paid Thru Dt',
            'receipt_id' => 'Receipt ID',
            'overage' => 'Overage',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceipt()
    {
        return $this->hasOne(Receipt::className(), ['id' => 'receipt_id']);
    }
}
