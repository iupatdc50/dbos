<?php

namespace app\models\accounting;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use app\models\value\Lob;
use Yii;

class AddTypeForm extends Model
{
	public $fee_types = [];
	public $lob_cd;
	public $new_fee_type;
	
	public function rules()
	{
		return [
				[['lob_cd', 'new_fee_type'], 'required'],
		];
	}
	
	public function attributeLabels()
	{
		return [
			'lob_cd' => 'Trade',
			'new_fee_type' => 'Fee Type',
		];
	}
	
	public function getLobOptions()
	{
		return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
	}
	
	public function getSerializedFeeTypes()
	{
		return implode('|', $this->fee_types);
	}
	
}