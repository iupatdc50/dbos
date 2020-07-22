<?php


namespace app\models\contractor;


class CurrentSignatoryAgreement extends Signatory
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CurrentSignatoryAgreements';
    }

    public static function primaryKey()
    {
        return ['id'];
    }


}