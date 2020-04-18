<?php

namespace app\models\member;

use app\models\base\BasePhone;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "MemberPhones".
 *
 * @property string $member_id
 *
 * @property Member $aggregate
 * @property PhoneDefault $isDefault
 */
class Phone extends BasePhone
{
	public $relationAttribute = 'member_id';
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberPhones';
    }
    
    public function createDefaultObj()
    {
    	return new PhoneDefault;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['member_id', 'phone'], 'unique', 'targetAttribute' => ['member_id', 'phone'], 'message' => 'The combination of Member and Phone has already been taken.']
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
     * @return ActiveQuery
     */
    public function getAggregate()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getIsDefault()
    {
    	return $this->hasOne(PhoneDefault::className(), ['phone_id' => 'id']);
    }

}
