<?php


namespace app\models\report;

use app\components\utilities\OpDate;
use yii\base\InvalidValueException;
use yii\base\Model;

class UniversalFileForm extends Model
{

    public $acct_month;

    public function rules()
    {
        return [
            [['acct_month'], 'required'],
            [['acct_month'], 'validateAcctMonth'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'acct_month' => 'Accounting Month',
        ];
    }

    public function validateAcctMonth($attribute, /** @noinspection PhpUnusedParameterInspection */
                                    $params)
    {
        if(!preg_match('/^\d{6}$/', $this->$attribute))
            $this->addError($attribute, 'Accounting Month must be and integer formatted `YYYYMM`');
        else {
            $report_dt = new OpDate();
            try {
                $report_dt->setDate(substr($this->$attribute, 0, 4), substr($this->$attribute, 4, 2), 01);
                $today = $this->getToday();
                if(OpDate::dateDiff($today, $report_dt) > 0)
                    $this->addError($attribute, 'Accounting Month cannot be future');
            } catch (InvalidValueException $e) {
                $this->addError($attribute, 'Invalid month and year');
            }
        }
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