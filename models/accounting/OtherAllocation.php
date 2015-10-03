<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "OtherAllocations".
 *
 * @property integer $receipt_id
 * @property string $member_id
 * @property string $fee_type
 * @property string $allocation_amt
 *
 * @property Receipts $receipt
 * @property FeeTypes $feeType
 */
class OtherAllocation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'OtherAllocations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id', 'member_id', 'fee_type', 'allocation_amt'], 'required'],
            [['receipt_id'], 'integer'],
            [['allocation_amt'], 'number'],
            [['member_id'], 'string', 'max' => 11],
            [['fee_type'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'receipt_id' => 'Receipt ID',
            'member_id' => 'Member ID',
            'fee_type' => 'Fee Type',
            'allocation_amt' => 'Allocation',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceipt()
    {
        return $this->hasOne(Receipts::className(), ['id' => 'receipt_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeTypes::className(), ['fee_type' => 'fee_type']);
    }
}
