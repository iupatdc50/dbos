<?php


namespace app\models\training;

/**
 * Class MemberCredDrug
 * @package app\models\training
 * Subtype for Drug Test
 *
 * @property DrugTestResult $drugTestResult
 */


class MemberCredDrug extends CurrentMemberCredential
{

    public static function credentialId()
    {
        return Credential::DRUG;
    }

    public static function find()
    {
        return new MemberCredQuery(get_called_class(), ['credential_id' => self::credentialId(), 'tableName' => self::tableName()]);
    }

    public function getDrugTestResult()
    {
        return $this->hasOne(DrugTestResult::className(), ['member_id' => 'member_id', 'credential_id' => 'credential_id', 'complete_dt' => 'complete_dt']);
    }
}