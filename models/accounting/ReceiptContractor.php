<?php

namespace app\models\accounting;

class ReceiptContractor extends ReceiptMultiMember
{

    public static function payorType()
    {
        return self::PAYOR_CONTRACTOR;
    }

    public static function find()
    {
        return new ReceiptQuery(get_called_class(), ['type' => self::payorType(), 'tableName' => self::tableName()]);
    }

    /**
     * @param $insert
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if(!isset($this->responsible) && ($this->responsible instanceof ResponsibleEmployer))
                throw new \yii\base\InvalidConfigException('No responsible employer object injected');
            if ($insert) {
                if (!isset($this->payor_nm))
                    $this->payor_nm = $this->responsible->employer->contractor;
                $this->payor_type = self::PAYOR_CONTRACTOR;
            }
            return true;
        }
        return false;
    }

    public function getHelperDuesText()
    {
        return ($this->helper_dues > 0.00) ? $this->helper_dues . ' (' . $this->helper_hrs . ' hours)' : null;
    }

    public function getCustomAttributes($forPrint = false)
    {
        $attrs = parent::getCustomAttributes($forPrint);
        $attrs[] = [
            'attribute' => 'helperDuesText',
            'label' => 'Helper Dues',
        ];
        return $attrs;
    }



}
