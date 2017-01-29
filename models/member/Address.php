<?php

namespace app\models\member;

use Yii;
use app\models\base\BaseAddress;
use app\helpers\OptionHelper;

/**
 * This is the model class for table "MemberAddresses".
 *
 * @property string $member_id
 *
 * @property Member $member
 */

class Address extends BaseAddress
{
	public $relationAttribute = 'member_id';
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberAddresses';
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
            'member_id' => 'Member ID',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAggregate()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }
    
    public function getAddressTypeOptions()
    {
    	return [
    			OptionHelper::ADDRESS_MAILING => 'Mailing',
    			OptionHelper::ADDRESS_LOCATION => 'Location',
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
