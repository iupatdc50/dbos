<?php

namespace app\models\accounting;

use app\components\utilities\OpDate;
use yii\base\Model;

/**
 * @property OpDate $today
 */
class CreditCardUpdateForm extends Model
{
    public $cardholder;
    public $card_id;
    public $month;
    public $year;

    public function rules()
    {
        return [
            [['month', 'year'], 'required'],
            [['year'], 'integer'],
            ['month', 'integer', 'min' => 1, 'max' =>12],
            ['month', 'validateExpireDt'],
            ['card_id', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'month' => 'MM',
            'year' => 'YYYY',
            'cardholder' => 'Cardholder',
        ];
    }

    public function validateExpireDt($attribute)
    {
        $dt = (new OpDate)->setFromMySql("$this->year-{$this->$attribute}-01");
        if (OpDate::dateDiff($this->today, $dt) < 0)
            $this->addError($attribute, 'Expire Date must be future');

    }

    /**
     * Override this function when testing with fixed date
     *
     * @return OpDate
     */
    protected function getToday()
    {
        return new OpDate();
    }

}