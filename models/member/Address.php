<?php

namespace app\models\member;

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
        return 'MemberAddresses';
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
    public function getMember()
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
}
