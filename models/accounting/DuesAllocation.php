<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "DuesAllocations".
 *
 * @property integer $receipt_id
 * @property string $member_id
 * @property string $allocation_amt
 * @property integer $months
 * @property string $paid_thru_dt
 *
 * @property Receipt $receipt
 * @property Member $member
 */
class DuesAllocation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DuesAllocations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id', 'member_id', 'allocation_amt', 'months', 'paid_thru_dt'], 'required'],
            [['receipt_id', 'months'], 'integer'],
            [['allocation_amt'], 'number'],
            [['paid_thru_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['member_id'], 'string', 'max' => 11]
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
            'allocation_amt' => 'Allocation',
            'months' => 'Months',
            'paid_thru_dt' => 'Paid Thru Date',
        ];
    }
    
    public function afterSave($insert, $changedAttributes) {
    	if (parent::afterSave($insert, $changedAttributes)) {
    		if (isset($changedAttributes['allocation_amt'])) {
    			// Set an event to check outstanding init balance
    		}
    	}
    		 
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
    public function getMember()
    {
        return $this->hasOne(Members::className(), ['member_id' => 'member_id']);
    }
}
