<?php

namespace app\models\member;

use yii\base\Model;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\value\Lob;

class CcForm extends Model
{
	public $effective_dt;
	public $other_local;
	public $reason;

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
        		'reamrks' => 'Remarks',
        ];
    }
	
}