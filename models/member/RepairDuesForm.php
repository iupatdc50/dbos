<?php

namespace app\models\member;

use yii\base\Model;

class RepairDuesForm extends Model
{
	public $alloc_id;
	public $paid_thru_dt;
	public $overage;

    public function rules()
	{
		return [
				[['alloc_id'], 'required'],
                ['paid_thru_dt', 'date', 'format' => 'php:Y-m-d'],
                ['overage', 'number'],
                ['overage', 'default', 'value'=> 0.00],
		];
	}
	
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        		'alloc_id' => 'Last Good Receipt',
        		'paid_thru_dt' => 'Dues Thru',
                'overage' => 'Starting Overage',
        ];
    }

}

