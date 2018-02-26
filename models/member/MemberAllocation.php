<?php

namespace app\models\member;

use app\models\accounting\Receipt;

/**
 * This is the model class for table "AllocExtended".
 *
 * @property integer $am_id
 * @property integer $receipt_id
 * @property string $received_dt
 * @property string $acct_month
 * @property string $member_id
 * @property string $fee_type
 * @property string $amt
 * @property integer $months
 * @property string $paid_thru_dt
 *
 * @property Receipt $receipt
 */
class MemberAllocation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AllocExtended';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['am_id', 'receipt_id', 'months'], 'integer'],
            [['receipt_id', 'received_dt', 'acct_month', 'member_id', 'fee_type', 'amt'], 'required'],
            [['received_dt', 'paid_thru_dt'], 'safe'],
            [['amt'], 'number'],
            [['acct_month'], 'string', 'max' => 6],
            [['member_id'], 'string', 'max' => 11],
            [['fee_type'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'am_id' => 'Am ID',
            'receipt_id' => 'Receipt ID',
            'received_dt' => 'Received Dt',
            'acct_month' => 'Acct Month',
            'member_id' => 'Member ID',
            'fee_type' => 'Fee Type',
            'amt' => 'Amt',
            'months' => 'Months',
            'paid_thru_dt' => 'Paid Thru Dt',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceipt()
    {
        return $this->hasOne(Receipt::className(), ['id' => 'receipt_id']);
    }

}
