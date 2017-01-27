<?php

namespace app\models\member;

use Yii;
use app\models\base\BasePhone;

/**
 * This is the model class for table "MemberPhones".
 *
 * @property string $member_id
 *
 * @property Member $member
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
    
    public static function createDefaultObj()
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
     * @return \yii\db\ActiveQuery
     */
    public function getAggregate()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIsDefault()
    {
    	return $this->hasOne(PhoneDefault::className(), ['phone_id' => 'id']);
    }

}
