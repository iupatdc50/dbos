<?php

namespace app\models\member;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use app\models\value\Size;
use app\helpers\OptionHelper;
use app\components\utilities\OpDate;
use app\models\base\iIdGeneratedInterface;
use app\models\base\iNotableInterface;
use app\models\value\TradeSpecialty;
use app\models\value\DocumentType;

/**
 * This is the model class for table "Members".
 *
 * @property string $member_id
 * @property string $ssnumber
 * @property string $report_id
 * @property string $last_nm
 * @property string $first_nm
 * @property string $middle_inits
 * @property string $suffix
 * @property string $birth_dt
 * @property string $gender
 * @property string $shirt_size
 * @property string $local_pac
 * @property string $hq_pac
 * @property string $remarks
 * @property string $photo_id Stored the generated filename
 * @property string $imse_id
 * @property date $application_dt
 * @property date $init_dt
 * @property date $dues_paid_thru_dt
 * 
 * @property Phone[] $phones
 * @property Address[] $addresses
 * @property Address $mailingAddress
 * @property Email[] $emails
 * @property Specialty[] $specialties
 * @property Status[] $statuses
 * @property Status $currentStatus
 * @property MemberClass[] $classes
 * @property MemberClass $currentClass
 * @property CurrentEmployment $employer
 * @property Note[] $notes
 */
class Member extends \yii\db\ActiveRecord implements iNotableInterface
{
	/*
	 * @var OpDate
	 */
	protected $_application_dt;
	
	/*
	 * @var OpDate
	 */
	protected $_dues_paid_thru_dt;
	
	/*
	 * @var OpDate How old the application date can be
	 */
	public $app_cutoff_dt;
	
	/*
	 * @var OpDate Acceptable age of applicant
	 */
	public $age_cutoff_dt;
	
	/* 
	 * @var iIdInterface idGenerator 
	 */
	public $idGenerator;
	
	/**
	 * @var mixed 	Stages the image to be uploaded 
	 */
	public $photo_file;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Members';
    }
    
    /**
     * Returns a set of members for Select2 picklist. Full name
     * is returned as text (id, text are required columns for Select2)
     * 
     * @param string|array $search Criteria used for partial member list. If an array, then member
     * 							   key will be a like search
     */
    public static function listAll($search)
    {
    	/* @var Query $query */
    	$query = new Query;
    	$query->select('member_id as id, full_nm as text')
    		->from('MemberPickList')
    		->limit(10)
    		->distinct();
    	if (ArrayHelper::isAssociative($search)) { 
    		if (isset($search['full_nm'])) {
    			$query->where(['like', 'full_nm', $search['full_nm']]);
    			unset($search['full_nm']);
    		}
    		$query->andWhere($search);
    	} elseif (!is_null($search)) 
    		$query->where(['like', 'full_nm', $search]);
    	$command = $query->createCommand();
    	return $command->queryAll();
    }
    
    public function init()
    {
    	$this->app_cutoff_dt = $this->getToday();
    	$this->app_cutoff_dt->modify('-1 year');
    	$this->age_cutoff_dt = $this->getToday();
    	$this->age_cutoff_dt->modify('-16 year');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['last_nm', 'first_nm', 'birth_dt', 'gender', 'shirt_size', 'local_pac', 'hq_pac', 'application_dt'], 'required'],
            [['birth_dt', 'application_dt', 'init_dt', 'dues_paid_thru_dt'], 'date', 'format' => 'php:Y-m-d'],
        	[['application_dt'], 'validateApplicationDt'],
        	[['birth_dt'], 'validateBirthDt'],
			[['gender'], 'in', 'range' => OptionHelper::getAllowedGender()],
        	[['local_pac', 'hq_pac'], 'in', 'range' => OptionHelper::getAllowedTF()],
            [['ssnumber', 'report_id'], 'string', 'max' => 11],
            [['last_nm', 'first_nm'], 'string', 'max' => 30],
            [['middle_inits', 'suffix'], 'string', 'max' => 7],
        	[['shirt_size'], 'exist', 'targetClass' => Size::className(), 'targetAttribute' => 'size_cd'],
            [['photo_id'], 'string', 'max' => 20],
        	[['photo_file'], 'file', 'mimeTypes' => 'image/jpeg'],
        	[['middle_inits', 'suffix', 'photo_id', 'imse_id'], 'default'],
        	[['ssnumber', 'imse_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'ssnumber' => 'SSN',
            'report_id' => 'Government ID',
            'last_nm' => 'Last Name',
            'first_nm' => 'First Name',
            'middle_inits' => 'Middle Inits',
            'suffix' => 'Suffix',
            'birth_dt' => 'Birth Date',
            'gender' => 'Gender',
            'shirt_size' => 'Shirt Size',
            'local_pac' => 'Local PAC',
            'hq_pac' => 'HQ PAC',
            'remarks' => 'Remarks',
            'photo_file' => 'Photo',
        	'imse_id' => 'IUPAT ID',
            'age' => 'Age',
            'addressTexts' => 'Address(es)',
        	'phoneTexts' => 'Phone(s)',
        	'emailTexts' => 'Email(s)',
        	'pacTexts' => 'PAC Participation',
        	'specialtyTexts' => 'Specialties',
        	'lob_cd' => 'Local',
        	'status' => 'Status',
        	'fullName' => 'Name',
        	'application_dt' => 'Application Date',
        	'init_dt' => 'Init Date (Current)',
        	'dues_paid_thru_dt' => 'Dues&nbsp;Thru',
        ];
    }
    
    public function validateApplicationDt($attribute, $params)
    {
    	$dt = (new OpDate)->setFromMySql($this->$attribute);
    	if (OpDate::dateDiff($this->app_cutoff_dt, $dt) <= 0)
    		$this->addError($attribute, 'Application date is too old.');
    	elseif (OpDate::dateDiff($this->today, $dt) > 0)
    	    $this->addError($attribute, 'Application date cannot be future');
    }
    
    public function validateBirthDt($attribute, $params)
    {
    	$dt = (new OpDate)->setFromMySql($this->$attribute);
    	if (OpDate::dateDiff($this->age_cutoff_dt, $dt) > 0)
    		$this->addError($attribute, 'Member under age limit');
    }
    
    public function beforeValidate() 
    {
    	if (parent::beforeValidate()) {
    		if ($this->isAttributeChanged('ssnumber')) {
    			$this->report_id = 'xxx-xx-' . substr($this->ssnumber, 7);
    		}
    		return true;
    	}
    	return false;
    }
    
    public function beforeSave($insert) 
    {
    	if (parent::beforeSave($insert)) {
    		if ($insert) {
    			try {
    				$this->member_id = $this->idGenerator->newId();
    			} catch (Exception $e) {
    				throw new \yii\base\InvalidConfigException('Missing ID generator');
    			}
    			if ($this->isAttributeChanged('application_dt') && ($this->dues_paid_thru_dt === null))
    				$this->dues_paid_thru_dt = $this->getDuesStartDt()->getMySqlDate();
    		}
    		return true;
    	}
    	return false;
    }
    
    public function afterSave($insert, $changedAttributes)
    {
    	if (parent::afterSave($insert, $changedAttributes)) {
    		if (isset($changedAttributes['application_dt']))
    			unset($this->_application_dt);
    	}
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZipCode()
    {
    	return $this->hasOne(ZipCode::className(), ['zip_cd' => 'zip_cd']);
    }  
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
    	return $this->hasMany(Address::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailingAddress()
    {
    	return $this->hasOne(Address::className(), ['member_id' => 'member_id'])->where("address_type = 'M'");
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhones()
    {
    	return $this->hasMany(Phone::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmails()
    {
    	return $this->hasMany(Email::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialties()
    {
    	return $this->hasMany(Specialty::className(), ['member_id' => 'member_id']);
    }
    
    public function getAvailableSpecialties()
    {
    	$sql = "SELECT specialty "
    		   . "  FROM " . TradeSpecialty::tableName()
    		   . "  WHERE lob_cd = :lob_cd "
    		   . "    AND specialty NOT IN (SELECT specialty FROM " . Specialty::tableName()
    		   . "                            WHERE member_id = :member_id) "
    		   . "  ORDER BY specialty "
    	;
    	$cmd = Yii::$app->db->createCommand($sql);
    	// When no member status entry exists, should return an empty resultset
    	$cmd->bindValues([
    			':lob_cd' => isset($this->currentStatus) ? $this->currentStatus->lob_cd : 'none',
    			':member_id' => $this->member_id,
    	]);
    	return $cmd->queryAll();
    }
    
    public function getUnfiledDocs()
    {
    	$sql = "SELECT doc_type "
    			. "  FROM " . DocumentType::tableName()
    			. "  WHERE doc_type NOT IN (SELECT doc_type FROM " . Document::tableName()
    			. "                            WHERE member_id = :member_id) "
    			. "  ORDER BY doc_type "
    	;
    	$cmd = Yii::$app->db->createCommand($sql);
    	$cmd->bindValues([
    			':member_id' => $this->member_id,
    	]);
    	return $cmd->queryAll();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSize()
    {
    	return $this->hasOne(Size::className(), ['size_cd' => 'shirt_size']);
    } 

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentStatus()
    {
    	return $this->hasOne(Status::className(), ['member_id' => 'member_id'])
    		->from(Status::tableName() . ' St')
    		->where('St.end_dt IS NULL');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatuses()
    {
        return $this->hasMany(Status::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorksFor()
    {
    	return $this->hasOne(Employment::className(), ['member_id' => 'member_id'])
    		->where('end_dt IS NULL');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentClass()
    {
    	return $this->hasOne(MemberClass::className(), ['member_id' => 'member_id'])
    		->from(MemberClass::tableName() . ' MC')
    		->where('MC.end_dt IS NULL');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClasses()
    {
        return $this->hasMany(MemberClass::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployer()
    {
    	return $this->hasOne(CurrentEmployment::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * Adds a journal note to this member
     * 
     * @param Note $note
     */
    public function addNote($note)
    {
    	if (!($note instanceof Note))
    		throw new \BadMethodCallException('Not an instance of MemberNote');
    	$note->member_id = $this->member_id;
    	return $note->save();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(Note::className(), ['member_id' => 'member_id'])->orderBy(['created_at' => SORT_DESC]);
    }
    
    public function getNoteCount()
    {
        return $this->hasMany(Note::className(), ['member_id' => 'member_id'])->count();
    }
    
    public function getSizeOptions()
    {
    	return ArrayHelper::map(Size::find()->orderBy('seq')->all(), 'size_cd', 'size_cd');
    }
    
    public function getFullName()
    {
    	return $this->last_nm . ', ' . 
    	       $this->first_nm . 
    	       (isset($this->middle_inits) ? ' ' . $this->middle_inits : '') .
    	       (isset($this->suffix) ? ', ' .$this->suffix : '')
    	;
    }
    
    public function getAddressTexts()
    {
    	$texts = [];
    	foreach ($this->addresses as $address) {
    		$texts[] = $address->getAddressText(true);
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    public function getPhoneTexts()
    {
    	$texts = [];
    	foreach ($this->phones as $phone) {
    		$texts[] = $phone->phoneText;
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    public function getEmailTexts()
    {
    	$texts = [];
    	foreach ($this->emails as $email) {
    		$texts[] = $email->email;
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    public function getPacTexts()
    {
    	$texts = [];
    	if ($this->local_pac == 'T')
    		$texts[] = 'Local';
    	if ($this->hq_pac == 'T')
    		$texts[] = 'HQ';
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    public function getSpecialtyTexts()
    {
    	$texts = [];
    	foreach ($this->specialties as $specialty) {
    		$texts[] = $specialty->specialty;
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    /**
     * Fetch photo file name with complete path (FQDN)
     * 
     * @return <string, NULL>
     */
    public function getImagePath()
    {
    	$path = Yii::getAlias('@webroot') . Yii::$app->params['imageDir'];
    	return isset($this->photo_id) ? $path . $this->photo_id : null;
    }
    
    /**
     * Fetch stored image URL
     * 
     * @return string
     */
    public function getImageUrl()
    {
    	$path =  Yii::$app->urlManager->baseUrl . Yii::$app->params['imageDir'];
    	return isset($this->photo_id) ? $path . $this->photo_id : $path . '_NotAvail.jpg';
    }
    
    /**
     * Process upload of image
     *
     * @return mixed the uploaded image instance
     */
    public function uploadImage() 
    {
        $image = UploadedFile::getInstance($this, 'photo_file');
        if (empty($image)) {
        	return false;
        }
        
        // generate a unique file name for storage
        $ext = end((explode(".", $image->name)));
        $this->photo_id = Yii::$app->security->generateRandomString(16).".{$ext}";
 
        return $image;
    }
    
    /**
     * Process deletion of image
     *
     * @return boolean the status of deletion
     */
    public function deleteImage() 
    {
        $file = $this->imagePath;
 
        // check if file exists on server
        if (empty($file) || !file_exists($file)) {
            return false;
        }
 
        // check if uploaded file can be deleted on server
        if (!unlink($file)) {
            return false;
        }
 
        // if deletion successful, reset your file attributes
        $this->photo_id = null;
 
        return true;
    }    
    
    public function getAge()
    {
    	$birth_dt = new OpDate();
    	return $birth_dt->setFromMySql($this->birth_dt)->diff($this->today)->format('%y');
    }
    
    /**
     * Starting dues paid thru date is based on the application date.  When on or 
     * prior to the 20th, the starting paid thru is the end of the previous month.
     * Otherwise it is the end of the current month.
     *  
     * @return \app\components\utilities\OpDate
     */
    public function getDuesStartDt()
    {
    	$dt = $this->getApplicationDtObject();
    	if ($dt->getDay() > 20) 
    		$dt->modify('+1 month');
    	$dt->setDate($dt->getYear(), $dt->getMonth(), 1);
    	$dt->modify('-1 day');
    	return $dt;
    }
    
    /**
     * A active member is "in application" if his initiation date is null for a new member,
     * and less than the application date if he has been assessed a new APF after reinstatement
     * 
     * @return boolean
     */
    public function isInApplication()
    {
    	$result = false;
    	if (isset($this->currentStatus) && ($this->currentStatus->member_status == 'A')) {
    		if (isset($this->init_dt)) {
	    		$application_dt = $this->getApplicationDtObject();
	    		$init_dt = (new OpDate)->setFromMySql($this->init_dt);
	    		$result = (OpDate::dateDiff($application_dt, $init_dt) < 0);
    		} else {
    			$result = true;
    		}
    	}
    	return $result;
    }
    
    public function getApplicationDtObject()
    {
    	if (!isset($this->_application_dt))
    		$this->_application_dt = (new OpDate)->setFromMySql($this->application_dt);
    	return $this->_application_dt;
    }
    
	/**
	 * Member is dues delinquent at 3 months as of the end of the current month
	 * 
	 * @throws \Exception
	 * @return boolean
	 */
    public function isDelinquentNotSuspended()
    {
    	$result = false;
    	if (isset($this->currentStatus) && ($this->currentStatus->member_status == 'A')) {
    		if (!isset($this->dues_paid_thru_dt)) 
    			throw new \Exception("Dues paid thru date is not set for member: {$member_id}");
    		$result = $this->isOlderThanCutoff(3);
    	}
    	return $result;
    }
    
	/**
	 * Member is candidate for drop if suspended and dues are 6 months as of the end of the current month
	 * 
	 * @throws \Exception
	 * @return boolean
	 */
    public function isPastGracePeriodNotDropped()
    {
    	$result = false;
    	if (isset($this->currentStatus) && ($this->currentStatus->member_status == 'S')) {
    		if (!isset($this->dues_paid_thru_dt))
    			throw new \Exception("Dues paid thru date is not set for member: {$member_id}");
    		$result = $this->isOlderThanCutoff(6);
    	}
    	return $result;	
    }
    
    protected function getDuesPaidThruDtObject()
    {
    	if (!isset($this->_dues_paid_thru_dt))
    		$this->_dues_paid_thru_dt = (new OpDate)->setFromMySql($this->dues_paid_thru_dt);
    	return $this->_dues_paid_thru_dt;
    }
    
    protected function isOlderThanCutoff($months)
    {
    	$cutoff = $this->getToday();
    	$cutoff->modify('-' . $months . ' month');
    	$cutoff->setToMonthEnd();
    	return (OpDate::dateDiff($this->getDuesPaidThruDtObject(), $cutoff) >= 0);
    }
    
    public function getGenderText()
    {
    	return OptionHelper::getGenderText($this->gender);
    }
    
    public function getHqPacText()
    {
    	return OptionHelper::getTFText($this->hq_pac);
    }
    
    public function getLocalPacText()
    {
    	return OptionHelper::getTFText($this->local_pac);
    }
    
    /**
     * Override this function when testing with fixed date
     * 
     * @return \app\components\utilities\OpDate
     */
    protected function getToday()
    {
    	return new OpDate();
    }
    
}
