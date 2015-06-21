<?php

namespace app\models\member;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\value\Lob;
use app\models\value\TradeSpecialty;

/**
 * This is the model class for table "MemberSpecialties".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $specialty
 *
 * @property TradeSpecialty $tradeSpecialty
 */
class Specialty extends \yii\db\ActiveRecord
{
	/*
	 * Injected Member object, used for creating new entries
	 */
	public $member;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberSpecialties';
    }
    
    public function init()
    {
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			// Allows for client validation, 'exist' core validator does not
        	[['specialty'], 'in', 'range' => TradeSpecialty::find()->select('specialty')->asArray()->column()],
            [['member_id', 'specialty'], 'unique', 'targetAttribute' => ['member_id', 'specialty'], 'message' => 'The combination of Member ID and Specialty has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'specialty' => 'Specialty',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
	    	if (!(isset($this->member) && ($this->member instanceof Member)))
	    		throw new InvalidConfigException('No member object injected');
    		if ($insert) 
    			$this->member_id = $this->member->member_id;
    		return true;
    	}
    	return false;
    }
    
    public function getSpecialtyOptions()
    {
    	return ArrayHelper::map($this->member->availableSpecialties, 'specialty', 'specialty');
    }
    
}
