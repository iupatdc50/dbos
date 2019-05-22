<?php


namespace app\models\training;

/**
 * Class MemberCredRespFit
 * @package app\models\training
 * Subtype for Respirator Fit Test
 *
 * @property MemberRespirator $memberRespirator
 */


class MemberCredRespFit extends CurrentMemberCredential
{

    public static function credentialId()
    {
        return Credential::RESP_FIT;
    }

    public static function find()
    {
        return new MemberCredQuery(get_called_class(), ['credential_id' => self::credentialId(), 'tableName' => self::tableName()]);
    }

    public function getMemberRespirator()
    {
        return $this->hasOne(MemberRespirator::className(), ['member_id' => 'member_id', 'credential_id' => 'credential_id', 'complete_dt' => 'complete_dt']);
    }
}