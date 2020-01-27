<?php
namespace app\models\accounting;

use yii\base\Model;

class CreateRemitForm extends Model
{
	public $license_nbr;
	public $lob_cd;
	public $remarks;
	
	public function rules()
	{
		return [
				[['license_nbr', 'lob_cd'], 'required'],
                ['remarks', 'safe'],
		];	
	}
	
	public function attributeLabels()
	{
		return [
				'license_nbr' => 'Contractor',
				'lob_cd' => 'Trade',
                'remarks' => 'Remarks',
		];
	}
	
}