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

    /**
     * @return bool
     * @throws \yii\db\StaleObjectException
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $dbErrors = $this->removeAllocations();
            if (empty($dbErrors)) {
                return true;
            } else {
                Yii::$app->session->addFlash('error', 'Could not remove member from receipt.  Check log for details. Code `AM020`');
                Yii::error("*** AM020 Allocation remove errors.  Errors: " . print_r($dbErrors, true));
            }
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $receipt = $this->receipt;
        if ($receipt instanceof ReceiptMember) {  // Assume only one alloc member

            // Change payor on member receipt only
            if (isset($changedAttributes['member_id']) && (!$insert))
                $receipt->payor_nm = $this->member->fullName;

            // Change lob_cd when allocation was reassigned to a member belonging to a different trade
            if ($receipt->lob_cd <> $this->member->currentStatus->lob_cd)
                $receipt->lob_cd = $this->member->currentStatus->lob_cd;

            if (!empty($receipt->dirtyAttributes))
                $receipt->save();
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

    public function getDuesAllocCount()
    {
        return $this->hasMany(DuesAllocation::className(), ['alloc_memb_id' => 'id'])->count('id');
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
            if (!$alloc->delete())
                $errors = array_merge($errors, $alloc->errors);
        }
        return $errors;
    }
    
}
