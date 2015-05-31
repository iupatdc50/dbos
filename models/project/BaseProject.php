<?php

namespace app\models\project;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\base\iIdInterface;
use app\helpers\OptionHelper;
use app\components\utilities\OpDate;
use app\models\contractor\AgreementType;
use app\models\base\iNotableInterface;

/**
 * This is the model class for table "Projects".
 *
 * @property string $project_id
 * @property string $project_nm
 * @property string $general_contractor
 * @property string $agreement_type
 * @property string $disposition
 * @property string $project_status
 * @property string $close_dt
 *
 * @property Address[] $addresses
 * @property Note[] $notes
 * @property BaseRegistration[] $registrations
 * @property BaseRegistration $awarded
 * @property AgreementType $agreementType
 */
class BaseProject extends \yii\db\ActiveRecord implements iNotableInterface
{
	protected $_validationRules = [];
	protected $_labels = [];
	
	/* 
	 * @var iIdInterface idGenerator 
	 */
	public $idGenerator;
	
	/**
	 * @var string Used for LMA/JTP filter
	 */
	protected $type_filter;
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Projects';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $common_rules = [
            [['project_nm'], 'required'],
            [['disposition'], 'in', 'range' => OptionHelper::getAllowedDisp()],
            [['project_status'], 'in', 'range' => OptionHelper::getAllowedStatus()],
        	[['close_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['project_nm', 'general_contractor'], 'string', 'max' => 100],
            [['agreement_type'], 'exist', 'targetClass' => AgreementType::className()],
        	[['close_dt', 'general_contractor'], 'default'],
        ];
        return array_merge($this->_validationRules, $common_rules);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $common_labels =  [
            'project_id' => 'Project ID',
            'project_nm' => 'Project Name',
            'general_contractor' => 'General',
            'agreement_type' => 'Type',
            'disposition' => 'Disposition',
            'close_dt' => 'Close Date',
            'addressTexts' => 'Address(es)',
        	'project_status' => 'Status',
        ];
        return array_merge($this->_labels, $common_labels);
    }

    public function beforeSave($insert) 
    {
    	if (parent::beforeSave($insert)) {
    		if ($insert) {
    			try {
    				$this->project_id = $this->idGenerator->newId();
    			} catch (Exception $e) {
    				throw new \yii\base\InvalidConfigException('Missing ID generator');
    			}
    		}
    		if ((!isset($this->close_dt) || trim($this->close_dt)==='') && ($this->project_status != OptionHelper::STATUS_ACTIVE)) {
    			$this->project_status = OptionHelper::STATUS_ACTIVE;
    		} elseif ((isset($this->close_dt)) && ($this->project_status == OptionHelper::STATUS_ACTIVE)) {
    			$this->project_status = OptionHelper::STATUS_CLOSED;
    		}
    			
    		return true;
    	}
    	return false;
    }
    
    public function getIsActive()
    {
    	return is_null($this->close_dt);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(Address::className(), ['project_id' => 'project_id']);
    }

	public function getStatusText()
	{
		return OptionHelper::getStatusText($this->project_status);
	}

	public function getDispText()
	{
		return OptionHelper::getDispText($this->disposition);
	}

	/**
     * Adds a journal note to this project
     * 
     * @param Note $note
     */
    public function addNote($note)
    {
    	if (!($note instanceof Note))
    		throw new \BadMethodCallException('Not an instance of ProjectNote');
    	$note->project_id = $this->project_id;
    	return $note->save();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(Note::className(), ['project_id' => 'project_id'])->orderBy(['created_at' => SORT_DESC]);
    }
    
    public function getNoteCount()
    {
        return $this->hasMany(Note::className(), ['project_id' => 'project_id'])->count();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistrations()
    {
    	return $this->hasMany(BaseRegistration::className(), ['project_id' => 'project_id']);
    }
    
    public function getAwarded()
    {    	
    	return $this->hasOne(AwardedBid::className(), ['project_id' => 'project_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreementType()
    {
        return $this->hasOne(AgreementType::className(), ['agreement_type' => 'agreement_type']);
    }
    
    public function getTypeOptions()
    {
    	return ArrayHelper::map(AgreementType::find()->where("agreement_type <> 'CN'")->all(), 'agreement_type', 'descrip');
    }
    
    public function getAddressTexts()
    {
    	$texts = [];
    	foreach ($this->addresses as $address) {
    		$texts[] = $address->getAddressText(true);
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    
}
