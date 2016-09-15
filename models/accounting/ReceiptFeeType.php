<?php

namespace app\models\accounting;

use Yii;
use app\modules\admin\models\FeeType;


/**
 * This is the model class for the view "ReceiptFeeTypes"
 *
 * @property integer $receipt_id
 * @property string @fee_type
 * 
 * @property FeeType $feeType
 *
 */
class ReceiptFeeType extends \yii\db\ActiveRecord
{
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'ReceiptFeeTypes';
	}
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getFeeType()
	{
		return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
	}
	
	
}
