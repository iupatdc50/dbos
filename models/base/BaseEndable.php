<?php

namespace app\models\base;

use Yii;
use app\components\utilities\OpDate;

class BaseEndable extends \yii\db\ActiveRecord
{

	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		if ($insert)
			$this->closePrevious();
	}
	
	protected function closePrevious()
	{
		/* @var $end_dt OpDate */
		$end_dt = (new OpDate())->setFromMySql($this->effective_dt)->sub(new \DateInterval('P1D'));
		$condition = "member_id = '{$this->member_id}' AND end_dt IS NULL AND effective_dt <> '{$this->effective_dt}'";
		call_user_func([$this->className(), 'updateAll'], ['end_dt' => $end_dt->getMySqlDate()], $condition);
	}
	
}