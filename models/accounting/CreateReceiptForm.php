<?php
namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use app\models\accounting\Receipt;
use app\models\value\Lob;

class CreateReceiptForm extends Model
{
	public $payor_type;
	public $member_id;
	public $license_nbr;
	public $lob_cd;
	
	public function rules()
	{
		return [
				[['payor_type'], 'required'],
				['member_id', 'required', 'when' => function($model) {
						return $model->payor_type == Receipt::PAYOR_MEMBER;
				}, 'whenClient' => "function (attribute, value) {
						return $('#payortype').val() == " . Receipt::PAYOR_MEMBER . ";
				}"],
				[['license_nbr', 'lob_cd'], 'required', 'when' => function($model) {
						return $model->payor_type == Receipt::PAYOR_CONTRACTOR;
				}, 'whenClient' => "function (attribute, value) {
						return $('#payortype').val() == " . Receipt::PAYOR_CONTRACTOR . ";
				}"],
		];	
	}
	
	public function attributeLabels()
	{
		return [
				'member_id' => 'Member',
 				'license_nbr' => 'Contractor',
				'lob_cd' => 'Trade',
		];
	}
	
	public function getLobOptions()
	{
		return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
	}
		
}

