<?php

namespace app\models\member;

use yii\base\Model;

class CcForm extends Model
{
	public $effective_dt;
	public $other_local;
	public $remarks;

	public function rules()
	{
		return [
				[['effective_dt', 'other_local'], 'required'],
				[['effective_dt'], 'date', 'format' => 'php:Y-m-d'],
				['remarks', 'string'],
		];
	}
	
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        		'effective_dt' => 'Effective',
        		'other_local' => 'Other Local',
        		'remarks' => 'Remarks',
        ];
    }
	
}