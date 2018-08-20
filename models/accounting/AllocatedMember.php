<?php

namespace app\models\accounting;

use Yii;
use app\models\member\Member;

/**
 * This is the model class for table "AllocatedMembers".
 *
 * @property integer $id
 * @property integer $receipt_id
 * @property string $member_id
 * 
 * @property Receipt $receipt
 * @property Member $member
 * @property BaseAllocation[] $allocations
 * @property CcOtherLocal $otherLocal
 */
class AllocatedMember extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AllocatedMembers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id'], 'integer'],
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
        	'totalAllocation' => 'Allocated',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (isset($changedAttributes['member_id'])) {
            // Change payor on member receipt only, assume only one alloc member
            if (!$insert) {
                $receipt = $this->receipt;
                if ($receipt instanceof ReceiptMember) {
                    $receipt-> payor_nm = $this->member->fullName;
                    $receipt->save();
                }
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceipt()
    {
    	return $this->hasOne(Receipt::className(), ['id' => 'receipt_id']);
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
    public function getAllocations()
    {
    	return $this->hasMany(BaseAllocation::className(), ['alloc_memb_id' => 'id']);
    }

    /**
     * @return int|string
     */
    public function getAllocationCount()
    {
        return $this->hasMany(BaseAllocation::className(), ['alloc_memb_id' => 'id'])->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOtherLocal()
    {
    	return $this->hasOne(CcOtherLocal::className(), ['alloc_memb_id' => 'id']);
    }
    
    public function getTotalAllocation()
    {
    	return $this->hasMany(BaseAllocation::className(), ['alloc_memb_id' => 'id'])
    					->where(['!=', 'fee_type', 'HR'])
    					->sum('allocation_amt')
    	;
    }

    /**
     * Line by line removal ensures that Allocation event triggers are fired
     *
     * @return array    If unsuccessful, array of errors
     * @throws \yii\db\StaleObjectException
     */
    public function removeAllocations()
    {
        $errors = [];
        foreach ($this->allocations as $alloc) {
            Yii::info('Class is: ' . get_class($alloc));
            if (!$alloc->delete())
                $errors = array_merge($errors, $obj->errors);
        }
        return $errors;
    }
    
}
