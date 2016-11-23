<?php

namespace app\models\accounting;

use Yii;
use app\models\member\Member;

class ReceiptMember extends Receipt
{
	public $other_local;
	protected $_remit_filter = 'member_remittable';
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$this->_validationRules = [
				[['other_local'], 'safe'],
		];
		return parent::rules();
	}
	
	/**
	 * Assume that member receipt applies to only one member
	 * 
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayingMember()
	{
		return $this->hasOne(Member::className(), ['member_id' => 'member_id'])
					->via('allocatedMembers')
		;
	}
	
	
	
}