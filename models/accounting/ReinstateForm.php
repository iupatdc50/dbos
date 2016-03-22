<?php

namespace app\models\accounting;

use yii\base\Model;
use Yii;

class ReinstateForm extends Model
{
	public $receipt_dt;
	public $total_amt;
	public $reinstate_fee;
	public $dues;
	public $other_fees;
	public $payment_method;
	public $notes;

	public function rules()
	{
		return [
				[['receipt_dt', 'total_amt'], 'required'],
				[['receipt_dt'], 'date', 'format' => 'php:Y-m-d'],
				[['total_amt', 'reinstate_fee', 'dues', 'other_fees'], 'number'],
				['notes', 'string'],
		];
	}
	
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        		
        ];
    }
	
    public function validateTotalAmt($attributes)
	{
		
	}
	
	
	
}