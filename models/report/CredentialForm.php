<?php


namespace app\models\report;

use yii\base\Model;

class CredentialForm extends Model
{

    CONST OPT_CERTIFICATE = 'C';
    CONST OPT_TRANSFER = 'T';

    public $member_id;
    public $option;

    public static function getAllowedOptions()
    {
        return [
            self::OPT_CERTIFICATE,
            self::OPT_TRANSFER,
        ];
    }

    public function rules()
    {
        return [
            [['option', 'member_id'], 'required'],
           [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
            [['option'], 'in', 'range' => self::getAllowedOptions()],
        ];
    }

    public function getOptionOptions()
    {
        return [
            self::OPT_TRANSFER => 'Transfer Form',
            self::OPT_CERTIFICATE => 'Certificate',
        ];
    }


}