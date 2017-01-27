<?php

namespace app\models\contractor;

use Yii;
use app\models\base\BasePhone;

/**
 * This is the model class for table "ContractorPhones".
 *
 * @property string $license_nbr
 *
 * @property Contractor $contractor
 */
class Phone extends BasePhone
{
	public $relationAttribute = 'license_nbr';
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ContractorPhones';
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
//            [['license_nbr'], 'required'],
//            [['license_nbr'], 'string', 'max' => 8],
            [['license_nbr', 'phone'], 'unique', 'targetAttribute' => ['license_nbr', 'phone'], 'message' => 'The combination of License No and Phone has already been taken.']
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIsDefault()
    {
    	return $this->hasOne(PhoneDefault::className(), ['phone_id' => 'id']);
    }

}
