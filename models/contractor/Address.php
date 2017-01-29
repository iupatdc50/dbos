<?php

namespace app\models\contractor;

use Yii;
use app\models\base\BaseAddress;
use app\helpers\OptionHelper;

/**
 * This is the model class for table "ContractorAddresses".
 *
 * @property string $license_nbr
 *
 * @property Contractor $contractor
 */

class Address extends BaseAddress
{
	public $relationAttribute = 'license_nbr';
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ContractorAddresses';
    }

    public function createDefaultObj()
    {
    	return new AddressDefault;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
        	[['address_type'], 'in', 'range' => [
    			OptionHelper::ADDRESS_MAILING,
    			OptionHelper::ADDRESS_LOCATION,
    			OptionHelper::ADDRESS_OTHER,
        	]],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'license_nbr' => 'License Nbr',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAggregate()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }
    
    public function getAddressTypeOptions()
    {
    	return [
    			OptionHelper::ADDRESS_MAILING => 'Mailing',
    			OptionHelper::ADDRESS_LOCATION => 'Location',
    			OptionHelper::ADDRESS_OTHER => 'Other',
    	];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIsDefault()
    {
    	return $this->hasOne(AddressDefault::className(), ['address_id' => 'id']);
    }

}
