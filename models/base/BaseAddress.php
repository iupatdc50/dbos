<?php

namespace app\models\base;

use Yii;
use app\models\ZipCode;
use app\helpers\OptionHelper;

/**
 * This is the base model class for Address classes.
 * 
 * ** NOTE: Class identified as `aggregate` in concrete class must implement getAddressDefault()
 *
 * @property integer $id
 * @property string $address_type
 * @property string $address_ln1
 * @property string $address_ln2
 * @property string $zip_cd
 *
 * @property ZipCode $zipCd
 */
abstract class BaseAddress extends \yii\db\ActiveRecord
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
            [['address_type', 'address_ln1', 'zip_cd'], 'required'],
            [['address_ln1', 'address_ln2'], 'string', 'max' => 50],
        	[['address_ln2'], 'default'],
			// Allows for client validation, 'exist' core validator does not
            [['zip_cd'], 'in', 'range' => ZipCode::find()->select('zip_cd')->asArray()->column()],
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
            'address_type' => 'Type',
            'address_ln1' => 'Address Line 1',
            'address_ln2' => 'Address Line 2',
            'zip_cd' => 'Zip Code',
            'addressText' => 'Address',
        ];
        return array_merge($this->_labels, $common_labels);
    }

    
    public function afterSave($insert, $changedAttributes)
    {
    	parent::afterSave($insert, $changedAttributes);
    	if (isset($changedAttributes['set_as_default'])) {
    		if ($this->set_as_default) {
    			$default = $this->aggregate->addressDefault;
    			if (!isset($default))
    				$default = self::createDefaultObj();
    			$this->makeDefault($default);
    		}
    		unset($this->set_as_default);
    	}
    }
    
    public function makeDefault($default)
    {
    	if (!($default instanceof AddressDefault))
    		throw new \BadMethodCallException('Not an instance of Phone Default');
    	$default->{$this->relationAttribute} = $this->{$this->relationAttribute};
    	$default->address_id = $this->id;
    	return $default->save();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZipCode()
    {
        return $this->hasOne(ZipCode::className(), ['zip_cd' => 'zip_cd']);
    }
    
    public function getTypeText()
    {
    	return OptionHelper::getAddressTypeText($this->address_type);
    }
    
    /**
     * 
     * @param boolean $type When true, includes address type in the label. Defaults to false. 
     * @return string
     */
    public function getAddressText($type = FALSE)
    {
    	return ($type ? $this->typeText . ': ' : '')
    		. implode(' ', [$this->address_ln1, $this->address_ln2, $this->zipCode->cityLn]);
    }
    
}
