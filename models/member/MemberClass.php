<?php

namespace app\models\member;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\member\ClassCode;
use app\models\value\RateClass;
use app\models\base\BaseEndable;

/**
 * This is the model class for table "MemberClasses".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $member_class
 * @property string $rate_class
 * @property integer $wage_percent
 *
 * @property MemberClass $mClass
 * @property RateClass $rClass
 */
class MemberClass extends BaseEndable
{
	CONST SCENARIO_CREATE = 'create';
	
	CONST APPRENTICE = 'A';
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberClasses';
    }
    
    public static function qualifier()
    {
    	return 'member_id';
    }

    /**
     * @var Member
     */
    public $member;
    
    /**
     * @var string ID of allowable class combination
     */
    public $class_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'effective_dt'], 'required'],
            [['effective_dt', 'end_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['wage_percent'], 'integer'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
            [['member_class'], 'exist', 'targetClass' => '\app\models\member\ClassCode', 'targetAttribute' => 'member_class_cd'],
            [['rate_class'], 'string', 'max' => 2],
            ['class_id', 'required', 'on' => self::SCENARIO_CREATE],
        	[['member_id', 'effective_dt'], 'unique', 'targetAttribute' => ['member_id', 'effective_dt'], 'message' => 'The combination of Member ID and Effective Dt has already been taken.']
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
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'member_class' => 'Member Class',
            'rate_class' => 'Rate Class',
            'wage_percent' => 'Wage Percent',
        	'class_id' => 'Class',
        ];
    }
    
    public function beforeValidate()
    {
    	if (parent::beforeValidate()) {
	    	if (empty($this->wage_percent))
	    		$this->wage_percent = 100;
    		return true;
    	}
    	return false;
    }

    /**
     * Populates member and rate classes from supplied allowable combination class 
     * 
     * If no combination class ID is supllied, member and rate class must be supplied
     * 
     * @return boolean
     */
    public function resolveClasses()
    {
    	if (isset($this->class_id)) {
	    	$combo_class = $this->comboClassDescrip;
	    	$this->member_class = $combo_class->member_class;
	    	$this->rate_class = $combo_class->rate_class;
    	}
    	if (empty($this->member_class) || empty($this->rate_class))
    		return false;
    	return true;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMClass()
    {
        return $this->hasOne(ClassCode::className(), ['member_class_cd' => 'member_class']);
    }
    
    public function getMClassDescrip()
    {
    	return $this->mClass->descrip . ($this->member_class == self::APPRENTICE ? ' [' . $this->wage_percent . '%]' : '');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRClass()
    {
        return $this->hasOne(RateClass::className(), ['rate_class' => 'rate_class']);
    }
    
    public function getComboClassDescrip()
    {
    	return $this->hasOne(ComboClassDescription::className(), ['class_id' => 'class_id']);
    }
    
    /**
     * Provides a picklist of allowable member and rate class code combinations
     * 
     * @return array
     */
    public function getClassOptions()
    {
    	return ArrayHelper::map(ComboClassDescription::find()->orderBy('class_descrip')->all(), 'class_id', 'class_descrip');
    }

}
