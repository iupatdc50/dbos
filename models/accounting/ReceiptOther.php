<?php

namespace app\models\accounting;

class ReceiptOther extends ReceiptMultiMember
{

    public static function payorType()
    {
        return self::PAYOR_OTHER;
    }

    public static function find()
    {
        return new ReceiptQuery(get_called_class(), ['type' => self::payorType(), 'tableName' => self::tableName()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['payor_nm'], 'required'],
        ];
        return parent::rules();
    }

    /**
     * @param $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->payor_type = self::PAYOR_OTHER;
            }
            return true;
        }
        return false;
    }

    public function getReceiptPayor()
    {
        $payor = parent::getReceiptPayor();
        if (isset($this->responsible))
            $payor .= ' (for ' . $this->responsible->employer->contractor . ')';
        return $payor;
    }
}
