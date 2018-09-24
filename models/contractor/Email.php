<?php

namespace app\models\contractor;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ContractorEmails".
 *
 * @property integer $id
 * @property string $license_nbr
 * @property string $email_type
 * @property string $email
 *
 */
class Email extends ActiveRecord
{
    CONST SCENARIO_CONTRACTOREXISTS = 'contexists';

    CONST TYPE_CONTACT = 'C';
    CONST TYPE_BILLING = 'B';
    CONST TYPE_OTHER = 'O';

    /*
     * Injected Contractor object, used for creating new entries
     */
    public $contractor;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ContractorEmails';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email_type'], 'in', 'range' => [
                self::TYPE_BILLING,
                self::TYPE_CONTACT,
                self::TYPE_OTHER,
            ]],
            [['license_nbr'], 'string', 'max' => 11],
            [['email'], 'required', 'on' => self::SCENARIO_CONTRACTOREXISTS],
            [['email'], 'email'],
            [['license_nbr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['license_nbr' => 'license_nbr']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'license_nbr' => 'License Nbr',
            'email_type' => 'Type',
            'email' => 'Email',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }

    public function getTypeOptions()
    {
        return [
            self::TYPE_CONTACT => 'Contact',
            self::TYPE_BILLING => 'Billing',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function getTypeText()
    {
        $options = $this->typeOptions;
        return isset($options[$this->email_type]) ? $options[$this->email_type] : "Unknown ({$this->email_type})";

    }

    /**
     * @param bool $type If true include the type label
     * @return string
     */
    public function getEmailText($type = FALSE)
    {
        return ($type ? $this->typeText . ': ' : '') . $this->email;
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if (!(isset($this->contractor) && ($this->contractor instanceof Contractor)))
                    throw new InvalidConfigException('No contractor object injected');
                $this->license_nbr = $this->contractor->license_nbr;
            }

            return true;
        }
        return false;
    }


}
