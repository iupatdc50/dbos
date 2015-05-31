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
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ContractorAddresses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
        	[['address_type'], 'in', 'range' => [
    			OptionHelper::ADDRESS_MAILING,
    			OptionHelper::ADDRESS_BILLING,
    			OptionHelper::ADDRESS_LOCATION,
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
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }
    
    public function getAddressTypeOptions()
    {
    	return [
    			OptionHelper::ADDRESS_MAILING => 'Mailing',
    			OptionHelper::ADDRESS_BILLING => 'Billing',
    			OptionHelper::ADDRESS_LOCATION => 'Location',
    	];
    }
    
}
