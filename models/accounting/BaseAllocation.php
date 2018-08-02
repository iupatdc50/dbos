<?php

namespace app\models\accounting;

use app\models\member\Status;
use app\models\member\Member;
use app\models\member\Standing;
use app\modules\admin\models\FeeType;

/** @noinspection PropertiesInspection */


/**
 * This is the base model class for various receipt allocation tables.
 *
 * @property integer $id
 * @property integer $alloc_memb_id
 * @property string $fee_type 
 * @property number $allocation_amt
 *
 * @property AllocatedMember $allocatedMember
 * @property Member $member
 * @property FeeType $feeType
 */
class BaseAllocation extends \yii\db\ActiveRecord
{
	protected $_validationRules = [];
	protected $_labels = [];
	
	/**
	 * @var Standing 	May be injected, if required
	 */
	public $standing;
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Allocations';
    }

    public static function allocTypes()
    {
        // must be overridden
    }

    public static function instantiate($row)
    {
        if ($row['fee_type'] == FeeType::TYPE_DUES)
            return new DuesAllocation();
        elseif (in_array($row['fee_type'], AssessmentAllocation::allocTypes()))
            return new AssessmentAllocation();
        return new self;
    }

	/**
	 * @inheritdoc
	*/
	public function rules()
	{
		$common_rules = [
				[['alloc_memb_id'], 'exist', 'targetClass' => AllocatedMember::className(), 'targetAttribute' => 'id'],
				[['allocation_amt'], 'required'],
				[['allocation_amt'], 'number'],	
				[['fee_type'], 'exist', 'targetClass' => FeeType::className(), 'targetAttribute' => 'fee_type'],
//				['allocation_amt', 'compare', 'compareValue' => 0.00, 'operator' => '>'],
		];
		return array_merge($this->_validationRules, $common_rules);
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$common_labels = [
				'fee_type' => 'Type',
 				'allocation_amt' => 'Allocation',
		];
		return array_merge($this->_labels, $common_labels);
	}

    /**
     * @return bool
     * @throws \yii\db\StaleObjectException
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->backOutMemberStatus();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws \yii\db\StaleObjectException
     */
    public function backOutMemberStatus()
    {
        $status = $this->member->currentStatus;
        if ($status->alloc_id === $this->id) {
            if($status->delete()) {
                Status::openLatest($this->member->member_id);
                return true;
            }
        }
        return false;
    }

    public function getUndoAllocation()
    {
        return $this->hasOne(UndoAllocation::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAllocatedMember()
    {
        return $this->hasOne(AllocatedMember::className(), ['id' => 'alloc_memb_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id'])
        			->via('allocatedMember')
        ;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeeType()
    {
    	return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
    }

    /**
     * Fee types that generate MemberStatus entries
     * @return array
     */
    public function getStatusGenerators()
    {
        return [
            FeeType::TYPE_CC,
            FeeType::TYPE_REINST,
            FeeType::TYPE_DUES,
        ];
    }

    /**
     * Looks up Member Status entry that was produced by this allocation
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMemberStatus()
    {
        return $this->hasOne(Status::className(), ['alloc_id' => 'id']);
    }
    
    protected function getStanding()
    {
    	if(!(isset($this->standing)))
    		$this->standing = new Standing(['member' => $this->member]);
    	return $this->standing;
    }
    
    
}