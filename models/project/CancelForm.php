<?php
namespace app\models\project;

use yii\base\Model;
use Yii;

class CancelForm extends Model
{
	public $cancel_dt;
	public $reason;
	
	public function rules()
	{
		return [
				[['cancel_dt', 'reason'], 'required'],
				[['cancel_dt'], 'date', 'format' => 'php:Y-m-d'],
		];
	}
	
	public function attributeLabels()
	{
		return [
				'cancel_dt' => 'Cancel Date',
				'reason' => 'Reason',
		];
	}
}