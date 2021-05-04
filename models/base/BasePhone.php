<?php

namespace app\models\base;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\components\validators\PhoneValidator;
use app\helpers\PhoneHelper;
use app\models\value\PhoneType;

/**
 * This is the base model class for Phone tables.
 * 
 * ** NOTE: Class identified as `aggregate` in concrete class must implement getPhoneDefault() 
 *
 * @property integer $id
 * @property string $phone
 * @property string $ext
 * @property string $phone_type
 *
 * @property PhoneType $phoneType
 * @property string $phoneText
 * @property ActiveRecord $isDefault
 */
abstract class BasePhone extends ActiveRecord
                         implements iDefaultableInterface
{
	protected $_validationRules = []; 
	protected $_labels = [];
	
	/** @var string Name of the attribute which will store the part-of class key */
    public $relationAttribute;
	
    public $set_as_default;
	
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
        	['set_as_default', 'safe'],
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
    
   public function beforeDelete()
    {
    	if (parent::beforeDelete()) {
            if ($this->isDefault) {
	    		Yii::$app->session->addFlash('error', 'Cannot delete default phone');
	    		return false;
	    	}
	    	return true;
    	}
    	return false;
    }
    
    public function afterSave($insert, $changedAttributes)
    {
    	parent::afterSave($insert, $changedAttributes);
        if ($this->set_as_default) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $default = $this->aggregate->phoneDefault;
    		if (!isset($default))
    			$default = $this->createDefaultObj();
    		if (!$this->makeDefault($default)) {
    			Yii::$app->session->addFlash('error', 'Problem changing phone default. Check log for details. Code `BP010`');
    			Yii::error("*** BP010  BasePhone::afterSave().  Messages: " . print_r($default->errors, true));
    		}	
    	}
    }

    public function makeDefault($default)
    {
    	$default->{$this->relationAttribute} = $this->{$this->relationAttribute};
    	$default->phone_id = $this->id;
        return $default->save();
    }

    public function getTypeOptions()
    {
    	return ArrayHelper::map(PhoneType::find()->all(), 'phone_type', 'descrip');
    }
    
    /**
     * @return ActiveQuery
     */
    public function getPhoneType()
    {
        return $this->hasOne(PhoneType::className(), ['phone_type' => 'phone_type']);
    }
    
    public function getPhoneText()
    {
    	return $this->phoneType->descrip . ': ' . $this->phone . (isset($this->ext) ? 'x' . $this->ext : '');
    }

    public abstract function getIsDefault();
    public abstract function getAggregate();
}
