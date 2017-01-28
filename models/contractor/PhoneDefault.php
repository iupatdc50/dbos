<?php

namespace app\models\contractor;

use Yii;

/**
 * This is the model class for table "ContractorPhonesDefault".
 *
 * @property string $license_nbr
 * @property integer $phone_id
 *
 * @property Contractor $contractor
 * @property Phones $phone
 */
class PhoneDefault extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ContractorPhonesDefault';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_nbr', 'phone_id'], 'required'],
            [['phone_id'], 'integer'],
            [['license_nbr'], 'string', 'max' => 8],
            [['license_nbr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['license_nbr' => 'license_nbr']],
            [['phone_id'], 'exist', 'skipOnError' => true, 'targetClass' => Phone::className(), 'targetAttribute' => ['phone_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'license_nbr' => 'License Nbr',
            'phone_id' => 'Phone ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhone()
    {
        return $this->hasOne(Phone::className(), ['id' => 'phone_id']);
    }
}
