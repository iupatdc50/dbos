<?php


namespace app\models\contractor;


class HistorySignatoryAgreement extends Signatory
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'HistorySignatoryAgreements';
    }

}