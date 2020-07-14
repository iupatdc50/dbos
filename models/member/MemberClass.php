<?php

namespace app\models\member;

use app\components\behaviors\OpImageBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use app\models\value\RateClass;
use app\models\base\BaseEndable;
use yii\web\UploadedFile;

/**
 * This is the model class for table "MemberClasses".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $member_class
 * @property string $rate_class
 * @property integer $wage_percent
 * @property string $doc_id
 *
 * @property ClassCode $mClass
 * @property RateClass $rClass
 * @property array $classOptions
 *
 * @method UploadedFile uploadImage()
 * @method getImagePath()
 */
class MemberClass extends BaseEndable
{
	CONST SCENARIO_CREATE = 'create';
	
	CONST APPRENTICE = 'A';
	CONST MATERIALHANDLER = 'M';

    /**
     * @var mixed	Stages document to be uploaded
     */
    public $doc_file;

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
     * Handles all the document attachment processing functions for the model
     *
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            OpImageBehavior::className(),
        ];
    }

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
        	['effective_dt', 'unique', 'targetAttribute' => ['member_id', 'effective_dt'], 'message' => 'The Effective Date has already been taken.'],
            [['doc_id'], 'string', 'max' => 20],
            [['doc_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'pdf, png'],
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
    	    /* @var ComboClassDescription $combo_class */
	    	$combo_class = $this->getComboClassDescrip()->one();
	    	$this->member_class = $combo_class->member_class;
	    	$this->rate_class = $combo_class->rate_class;
    	}
    	if (empty($this->member_class) || empty($this->rate_class))
    		return false;
    	return true;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getMClass()
    {
        return $this->hasOne(ClassCode::className(), ['member_class_cd' => 'member_class']);
    }
    
    public function getMClassDescrip()
    {
    	return $this->mClass->descrip . (($this->member_class == self::APPRENTICE || $this->member_class == self::MATERIALHANDLER) ? ' [' . $this->wage_percent . '%]' : '');
    }
    
    /**
     * @return ActiveQuery
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
