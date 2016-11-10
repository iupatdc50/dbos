<?php
namespace app\models\accounting;

use yii\base\Model;
use Yii;

class CreateRemitForm extends Model
{
	public $license_nbr;
	public $lob_cd;
	
	public function rules()
	{
		return [
				[['license_nbr', 'lob_cd'], 'required'],
		];	
	}
	
	public function attributeLabels()
	{
		return [
				'license_nbr' => 'Contractor',
				'lob_cd' => 'Trade',
		];
	}
	
}