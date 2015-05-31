<?php

namespace app\models\project\jtp;

use Yii;
use app\models\project\jtp\HoldAmount;
use app\models\project\jtp\JtpPayment;

/**
 * 
 * @property HoldAmount $holdAmount
 * @property Payment[] $payments
 *
 */

class Project extends \app\models\project\BaseProject
{
	public $type_filter = 'JTP';
	
    /**
     * @return \yii\db\ActiveQuery
     */
	public function getHoldAmount()
	{
		return $this->hasOne(HoldAmount::className(), ['project_id' => 'project_id']);
	}
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayments()
	{
		return $this->hasMany(Payment::className(), ['project_id' => 'project_id']);
	}
		
}