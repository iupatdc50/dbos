<?php

namespace app\models\accounting;

use Yii;
use app\models\contractor\Contractor;

/**
 * 
 * @property Contractor $contractor
 *
 */
class ReceiptContractor extends Receipt
{
	public $license_nbr;
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = parent::rules();
		$rules[] = [['license_nbr'], 'required'];
		return $rules;
	}
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getContractor()
	{
		return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
	}
	
	
}
