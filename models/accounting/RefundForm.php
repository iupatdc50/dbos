<?php

namespace app\models\accounting;

use yii\base\Model;

class RefundForm extends Model
{
    public $receipt_id;
    public $charge_id;

    public function rules()
    {
        return [
            [['receipt_id', 'charge_id'], 'required'],
        ];
    }
}