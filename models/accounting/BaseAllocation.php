<?php

namespace app\models\accounting;

use Yii;
use app\models\member\Member;
use app\models\member\Standing;
use \app\modules\admin\models\FeeType;

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
    
    protected function getStanding()
    {
    	if(!(isset($this->standing)))
    		$this->standing = new Standing(['member' => $this->member]);
    	return $this->standing;
    }
    
    
}