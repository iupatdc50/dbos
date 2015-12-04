<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the base model class for various receipt allocation tables.
 *
 * @property integer $receipt_id
 * @property string $member_id
 * @property number $allocation_amt
 *
 * @property Receipt $receipt
 */
class BaseAllocation extends \yii\db\ActiveRecord
{
	protected $_validationRules = [];
	protected $_labels = [];
	
	/**
	 * @inheritdoc
	*/
	public function rules()
	{
		$common_rules = [
				[['allocation_amt'], 'required'],
				[['receipt_id'], 'integer'],
				[['allocation_amt'], 'number'],	
				['allocation_amt', 'compare', 'compareValue' => 0.00, 'operator' => '>'],
		];
		return array_merge($this->_validationRules, $common_rules);
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$common_labels = [
				'id' => 'ID',
            	'receipt_id' => 'Receipt ID',
            	'member_id' => 'Member ID',
				'allocation_amt' => 'Allocation',
		];
		return array_merge($this->_labels, $common_labels);
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
	public function getFeeType()
	{
		return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
	}
	
}