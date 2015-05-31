<?php

namespace app\models\base;

use Yii;
use yii\helpers\ArrayHelper;
use app\components\validators\PhoneValidator;
use app\helpers\PhoneHelper;
use app\models\value\PhoneType;

/**
 * This is the base model class for Phone tables.
 *
 * @property integer $id
 * @property string $phone
 * @property string $ext
 * @property string $phone_type
 *
 * @property PhoneType $phoneType
 */
class BasePhone extends \yii\db\ActiveRecord
{
	protected $_validationRules = []; 
	protected $_labels = [];
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $common_rules = [
            [['phone', 'phone_type'], 'required'],
        	[['phone'], PhoneValidator::className()],
            [['ext'], 'string', 'max' => 7],
			// Allows for client validation, 'exist' core validator does not
            [['phone_type'], 'in', 'range' => PhoneType::find()->select('phone_type')->asArray()->column()],
        	[['ext'], 'default'],
        ];
        return array_merge($this->_validationRules, $common_rules);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $common_labels = [
            'id' => 'ID',
        	'phone' => 'Phone',
            'ext' => 'Ext',
            'phone_type' => 'Type',
        ];
        return array_merge($this->_labels, $common_labels);
    }
    
    public function beforeValidate() {
    	if (parent::beforeValidate()) {
    		if ($this->isAttributeChanged('phone'))
		    	$this->phone = PhoneHelper::format_us($this->phone);
    		return true;
    	}
    	return false;		    	
    }

    public function getTypeOptions()
    {
    	return ArrayHelper::map(PhoneType::find()->all(), 'phone_type', 'descrip');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoneType()
    {
        return $this->hasOne(PhoneType::className(), ['phone_type' => 'phone_type']);
    }
    
    public function getPhoneText()
    {
    	return $this->phoneType->descrip . ': ' . $this->phone . (isset($this->ext) ? 'x' . $this->ext : '');
    }
}
