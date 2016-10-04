<?php

namespace app\models\accounting;

use Yii;
use app\models\member\Member;

class ReceiptMember extends Receipt
{
	protected $_remit_filter = 'member_remittable';
	
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