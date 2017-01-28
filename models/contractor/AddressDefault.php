<?php

namespace app\models\contractor;

use Yii;

/**
 * This is the model class for table "ContractorAddressesDefault".
 *
 * @property string $license_nbr
 * @property integer $address_id
 *
 * @property Contractor $contractor
 * @property Address $address
 */
class AddressDefault extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ContractorAddressesDefault';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_nbr', 'address_id'], 'required'],
            [['address_id'], 'integer'],
            [['license_nbr'], 'string', 'max' => 8],
            [['license_nbr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['license_nbr' => 'license_nbr']],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::className(), 'targetAttribute' => ['address_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'license_nbr' => 'License Nbr',
            'address_id' => 'Address ID',
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
    public function getAddress()
    {
        return $this->hasOne(ContractorAddress::className(), ['id' => 'address_id']);
    }
}
