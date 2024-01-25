<?php

namespace app\models\member;

use app\models\accounting\DuesAllocation;
use app\models\base\iDemographicInterface;
use app\models\base\iIdInterface;
use app\models\employment\CurrentEmployment;
use app\models\employment\Employment;
use app\models\training\Credential;
use app\models\training\WorkHoursSummary;
use app\models\training\WorkProcess;
use app\models\ZipCode;
use BadMethodCallException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use app\models\value\Size;
use app\helpers\OptionHelper;
use app\components\utilities\OpDate;
use app\models\base\iNotableInterface;
use app\models\value\TradeSpecialty;
use app\models\value\DocumentType;
use app\models\accounting\Assessment;
use app\models\accounting\ApfAssessment;
use app\models\accounting\LastDuesReceipt;
use app\models\training\CurrentMemberCredential;
use app\modules\admin\models\FeeType;
use app\helpers\SsnHelper;
use app\components\validators\SsnValidator;
use yii\db\Exception;


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
 * @property string $ncfs_id
 * @property string $hq_pac
 * @property string $remarks
 * @property string $photo_id Stored the generated filename
 * @property string $imse_id
 * @property string $application_dt
 * @property string $init_dt
 * @property string $dues_paid_thru_dt
 * @property string $drug_test_dt
 * @property number $overage
 * @property string $card_id [varchar(20)]
 * @property string $nick_nm [varchar(30)]
 * @property string $stripe_id [varchar(50)]
 *
 * @property string $fullName
 * @property Phone[] $phones
 * @property PhoneDefault $phoneDefault
 * @property Phone $defaultPhone
 * @property Address[] $addresses
 * @property AddressDefault $addressDefault
 * @property Address $mailingAddress
 * @property Email $defaultEmail
 * @property Email[] $emails
 * @property integer $emailCount
 * @property Specialty[] $specialties
 * @property Status[] $statuses
 * @property Status $currentStatus
 * @property Status inServicePeriod
 * @property MemberReinstateStaged $reinstateStaged
 * @property MemberClass[] $classes
 * @property MemberClass $currentClass
 * @property QualifiesForIncrease $qualifiesForIncrease
 * @property Classification $classification
 * @property CurrentEmployment $employer
 * @property Employment $employerActive
 * @property Document $recurCcAuth
 * @property FeeBalance[] $feeBalances
 * @property float $totalFeeBalance
 * @property integer $ccgBalanceCount
 * @property float $allBalance
 * @property DuesAllocation[] $duesAllocations
 * @property ApfAssessment $currentApf
 * @property LastDuesReceipt $lastDuesReceipt
 * @property Note[] $notes
 * @property WorkProcess[] $processes
 * @property WorkHoursSummary[] $workHoursSummary
 * @property integer $expiredCount
 * @property integer $noteCount
 * @property string $imagePath
 * @property string $imageUrl
 * @property string $genderText
 * @property array $sizeOptions
 * @property OpDate $today
 * @property OpDate duesPaidThruDtObject
 * @property MemberLogin $enrolledOnline
 * @property Subscription $subscription
 * @property Document[] $unfiledDocs
 *
 */
class Member extends ActiveRecord implements iNotableInterface, iDemographicInterface
{
	CONST UNCHECKED = 0;
	CONST CHECKED = 1;
	
	CONST SCENARIO_CREATE = 'create';
	
	CONST MONTHS_GRACE_PERIOD = 6;
	CONST MONTHS_DELINQUENT = 3;

	CONST CUTOFF_DAY = 31; // 1/24/2024 Policy change. No cutoff for dues start following month
	
	/**
	 * @var OpDate
	 */
	protected $_application_dt;
	
	/**
	 * @var OpDate
	 */
	protected $_dues_paid_thru_dt;

    /**
     * @var WorkProcess[]
     */
	protected $_processes;
	
	/**
	 * @var OpDate How old the application date can be
	 */
	public $app_cutoff_dt;
	
	/**
	 * @var OpDate Acceptable age of applicant
	 */
	public $age_cutoff_dt;
	
	/**
	 * @var iIdInterface idGenerator
	 */
	public $idGenerator;
	
	/**
	 * @var mixed 	Stages the image to be uploaded 
	 */
	public $photo_file;
	
	/**
	 * @var mixed	Stages combined member & rate class codes
	 */
	public $member_class;
	public $wage_percent;
	
	/**
	 * @var boolean Stages whether new member should be APF exempt
	 */
	public $exempt_apf;

    /**
     * @var boolean Stages whether new member is a CC deposit
     */
    public $is_ccd;

    /**
     * @var Standing 	May be injected, if required
     */
    public $standing;

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
     * @param string|array $search      Criteria used for partial member list. If an array, then member
     *                                  key will be a like search
     * @return array
     * @throws Exception
     * @todo Consider remiving this function
     * @noinspection DuplicatedCode
     */
    public static function listAll($search)
    {
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

    /**
     * Returns a set of members for Select2 picklist. Full name
     * is returned as text (id, text are required columns for Select2)
     *
     * @param string|array $search      Criteria used for partial member list. If an array, then member
     *                                  key will be a like search
     * @return array
     * @throws Exception
     * @noinspection DuplicatedCode
     */
    public static function listSsnAll($search)
    {
        $query = new Query;
        $query->select('member_id as id, full_nm as text')
            ->from('MemberSsnPickList')
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

    /**
     * @throws \Exception
     */
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
            [['birth_dt', 'application_dt', 'init_dt', 'dues_paid_thru_dt', 'drug_test_dt'], 'date', 'format' => 'php:Y-m-d'],
			/*
        	[['application_dt'], 'validateApplicationDt', 'when' => function ($model, $attribute) {
        		return $model->{$attribute} !== $model->getOldAttribute($attribute);
        	}],
        	*/
        	[['application_dt'], 'validateApplicationDt', 'on' => self::SCENARIO_CREATE],
        	[['birth_dt'], 'validateBirthDt'],
			[['gender'], 'in', 'range' => OptionHelper::getAllowedGender()],
        	[['local_pac', 'hq_pac'], 'in', 'range' => OptionHelper::getAllowedTF(true)],
        	['ssnumber', SsnValidator::className()],
            [['report_id'], 'string', 'max' => 11],
            [['last_nm', 'first_nm'], 'string', 'max' => 30],
            [['middle_inits', 'suffix'], 'string', 'max' => 7],
        	[['shirt_size'], 'exist', 'targetClass' => Size::className(), 'targetAttribute' => 'size_cd'],
            [['photo_id'], 'string', 'max' => 20],
        	[['photo_file'], 'file', 'mimeTypes' => 'image/jpeg'],
        	[['middle_inits', 'suffix', 'photo_id', 'imse_id', 'ncfs_id', 'nick_nm'], 'default'],
            ['overage', 'default', 'value' => 0.00],
        	[['ssnumber', 'imse_id', 'ncfs_id'], 'unique'],
            [['exempt_apf', 'is_ccd', 'wage_percent'], 'safe'],
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
        	'ncfs_id' => 'NCFS ID',
        	'pacTexts' => 'PAC Participation',
        	'specialtyTexts' => 'Specialties',
        	'lob_cd' => 'Local',
        	'status' => 'Status',
        	'fullName' => 'Name',
        	'application_dt' => 'Application Date',
        	'init_dt' => 'Init Date (Current)',
        	'dues_paid_thru_dt' => 'Dues&nbsp;Thru',
        	'drug_test_dt' => 'Last Drug Test',
        	'exempt_apf' => 'Exempt APF?',
        	'overage' => 'Overage',
            'nick_nm' => 'Nick Name',
            'is_ccd' => 'Is CCD?',
            'stripe_id' => 'Stripe Customer ID'
        ];
    }
    
    public function validateApplicationDt($attribute, /** @noinspection PhpUnusedParameterInspection */
                                          $params)
    {
    	$dt = (new OpDate)->setFromMySql($this->$attribute);
    	if (OpDate::dateDiff($this->app_cutoff_dt, $dt) <= 0)
    		$this->addError($attribute, 'Application date is too old.');
    	elseif (OpDate::dateDiff($this->today, $dt) > 0)
    	    $this->addError($attribute, 'Application date cannot be future');
    }
    
    public function validateBirthDt($attribute, /** @noinspection PhpUnusedParameterInspection */
                                    $params)
    {
    	$dt = (new OpDate)->setFromMySql($this->$attribute);
    	if (OpDate::dateDiff($this->age_cutoff_dt, $dt) > 0)
    		$this->addError($attribute, 'Member under age limit');
    }
    
    public function beforeValidate() 
    {
    	if (parent::beforeValidate()) {
    		if ($this->isAttributeChanged('ssnumber')) {
    			$this->ssnumber = SsnHelper::format_us($this->ssnumber);
    		}
    		return true;
    	}
    	return false;
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function beforeSave($insert) 
    {
    	if (parent::beforeSave($insert)) {
    		if ($insert) {
    			try {
    				$this->member_id = $this->idGenerator->newId();
    				if($this->ssnumber == '000-00-0000')
    					$this->ssnumber = '000-00-' . substr($this->member_id, 4, 4);
    			} catch (\Exception $e) {
    				throw new InvalidConfigException('Missing ID generator');
    			}
    			if ($this->isAttributeChanged('application_dt') && ($this->dues_paid_thru_dt === null))
    				$this->dues_paid_thru_dt = $this->getDuesStartDt()->getMySqlDate();
    		} else {
    			// Don't override if dues_paid_thru_dt was reset
    			if ($this->isAttributeChanged('application_dt') && (!$this->isAttributeChanged('dues_paid_thru_dt')))
    				$this->dues_paid_thru_dt = $this->getDuesStartDt()->getMySqlDate();
    		}
    		if ($this->isAttributeChanged('ssnumber')) {
    			$this->report_id = 'xxx-xx-' . substr($this->ssnumber, 7);
    		}
    		return true;
    	}
    	return false;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
    	parent::afterSave($insert, $changedAttributes); 
    	if (isset($changedAttributes['application_dt'])) {
    		unset($this->_application_dt);
    	}
    }
    
    /**
     * @return ActiveQuery
     */
    public function getZipCode()
    {
    	return $this->hasOne(ZipCode::className(), ['zip_cd' => 'zip_cd']);
    }  
    
    /**
     * @return ActiveQuery
     */
    public function getAddresses()
    {
    	return $this->hasMany(Address::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getAddressDefault()
    {
    	return $this->hasOne(AddressDefault::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getMailingAddress()
    {
    	return $this->hasOne(Address::className(), ['member_id' => 'member_id'])->where("address_type = 'M'");
    }
    
    /**
     * @return ActiveQuery
     */
    public function getPhones()
    {
    	return $this->hasMany(Phone::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getPhoneDefault()
    {
    	return $this->hasOne(PhoneDefault::className(), ['member_id' => 'member_id']);
    }
    
    /**
     * Assume 1 email by policy
     *
     * @return ActiveQuery
     */
    public function getDefaultEmail()
    {
    	return $this->hasOne(Email::className(), ['member_id' => 'member_id']);
    }

    /**
     * @param Email $email
     * @return bool
     * @throws \yii\base\Exception
     */
    public function addEmail(Email $email)
    {
        if(!isset($email->member))
            $email->member = $this;
        if ($email->validate())
            return $email->save();
        throw new \yii\base\Exception('Invalid email. Errors: ' . print_r($email->errors, true));
    }

    /**
     * @deprecated Should only have a single email by policy
     * @return ActiveQuery
     */
    public function getEmails()
    {
        return $this->hasMany(Email::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return bool|int|string|null
     */
    public function getEmailCount()
    {
        return $this->hasMany(Email::className(), ['member_id' => 'member_id'])->count();
    }
    
    /**
     * @return ActiveQuery
     */
    public function getSpecialties()
    {
    	return $this->hasMany(Specialty::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return array
     * @throws Exception
     */
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

    /**
     * @param string $catg
     * @return array
     * @throws Exception
     */
    public function getUnfiledDocs($catg = DocumentType::CATG_MEMBER)
    {
    	$sql = "SELECT doc_type "
    			. "  FROM " . DocumentType::tableName()
    			. "  WHERE catg = :catg  "
    			. "    AND doc_type NOT IN (SELECT doc_type FROM " . Document::tableName()
    			. "                            WHERE member_id = :member_id) "
    			. "  ORDER BY doc_type "
    	;
    	$cmd = Yii::$app->db->createCommand($sql);
    	$cmd->bindValues([
    			':member_id' => $this->member_id,
                ':catg' => $catg,
    	]);
    	return $cmd->queryAll();
    }
    
    /**
     * @return ActiveQuery
     */
    public function getSize()
    {
    	return $this->hasOne(Size::className(), ['size_cd' => 'shirt_size']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCurrentStatus()
    {
    	return $this->hasOne(Status::className(), ['member_id' => 'member_id'])
    		->from(Status::tableName() . ' St')
    		->where('St.end_dt IS NULL');
    }

    /**
     * @param MemberReinstateStaged $stage
     */
    public function addReinstateStaged(MemberReinstateStaged $stage)
    {
        $stage->member_id = $this->member_id;
        $stage->save();
    }

    /**
     * @return ActiveQuery
     */
    public function getReinstateStaged()
    {
        return $this->hasOne(MemberReinstateStaged::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return bool
     */
    public function removeReinstateStaged()
    {
        if (isset($this->reinstateStaged)) {
            try {
                $this->reinstateStaged->delete();
            } catch (Throwable $e) {
                Yii::error("*** ME020 Reinstate stage for member `$this->member_id` $this->fullName could not be deleted.  Error(s): " . print_r($this->reinstateStaged->errors, true));
                Yii::$app->session->addFlash('error', 'Unable to complete transaction [Code: ME020]');
                return false;
            }
        }
        return true;
    }

    /**
     * Query for a grant in-svc status that would forgive qualifying dues during that period
     * Status must effective after member's current dues paid thru date
     * 
     * @return ActiveQuery
     */
    public function getInServicePeriod()
    {
    	return $this->hasOne(Status::className(), ['member_id' => 'member_id'])
    		->from(Status::tableName() . ' St')
    		->where(['and', 
    					['St.member_status' => Status::GRANTINSVC],
    					['or', "St.end_dt > '$this->dues_paid_thru_dt'" , ['St.end_dt' => null]]
    		]);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getStatuses()
    {
        return $this->hasMany(Status::className(), ['member_id' => 'member_id']);
    }

    /**
     * Adds a Status entry for a member
     *
     * For a new member member_status is determined by the submitted exempt_apf switch, and the lob_cd must be supplied.
     * For an existing member, the previous lob_cd is assumed
     *
     * @param Status $status
     * @param array $config
     * @return bool
     * @throws BadMethodCallException
     */
    public function addStatus(Status $status, /** @noinspection PhpUnusedParameterInspection */ $config = [])
    {
    	$status->member_id = $this->member_id;
    	if (!isset($status->effective_dt))
    	    $status->effective_dt = $this->getToday()->getMySqlDate();
    	if (!isset($status->lob_cd)) {
    		if (!isset($this->currentStatus))
    			throw new BadMethodCallException('No local can be determined for new Status');
    		$status->lob_cd = $this->currentStatus->lob_cd;
    	}
    	if ($status->reason == Status::REASON_NEW)
    		$status->member_status = ($this->exempt_apf == Member::CHECKED) ? Status::ACTIVE : Status::IN_APPL;

        return $status->save();
    }

    public function backOutStatus($alloc_id)
    {
        /*
        $status = $this->currentStatus;
        if(isset($status) && ($status->fee_type))
        */
    }

    /**
     * @return ActiveQuery
     */
    public function getWorksFor()
    {
    	return $this->hasOne(Employment::className(), ['member_id' => 'member_id'])
    		->where('end_dt IS NULL');
    }

    public function getEnrolledOnline()
    {
        return $this->hasOne(MemberLogin::className(), ['member_id' => 'member_id']);
    }

    /**
     * Enrollment for credit card auto-pay
     *
     * @return ActiveQuery
     */
    public function getSubscription()
    {
        return $this->hasOne(Subscription::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCurrentClass()
    {
    	return $this->hasOne(MemberClass::className(), ['member_id' => 'member_id'])
    		->from(MemberClass::tableName() . ' MC')
    		->where('MC.end_dt IS NULL');
    }
    
    /**
     * @return ActiveQuery
     */
    public function getClasses()
    {
        return $this->hasMany(MemberClass::className(), ['member_id' => 'member_id']);
    }

    /**
     * @param MemberClass $class
     * @param array $config
     * @return bool|string
     */
    public function addClass(MemberClass $class, /** @noinspection PhpUnusedParameterInspection */ $config = [])
    {
    	$class->member_id = $this->member_id;
    	if ($class->resolveClasses()) {
    	    if (!($result = $class->save()))
	    		$result = 'Could not save added class'; // return error message
    		return $result;
    	}
    	return false;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getClassification()
    {
    	return $this->hasOne(Classification::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getQualifiesForIncrease()
    {
        return $this->hasOne(QualifiesForIncrease::className(), ['member_id' => 'member_id']);
    }

    /**
     * Checks for minimum components necessary to bill or receive payment
     *
     * @return array
     */
    public function checkProfile()
    {
        $messages = [];
        if (!isset($this->currentStatus))
            $messages[] = 'Cannot identify local union.  Check Status panel.';
        if (!isset($this->currentClass))
            $messages[] = 'Cannot identify rate class.  Check Class panel.';
        return $messages;
    }

    /**
     * @return ActiveQuery
     */
    public function getWorkHoursSummary()
    {
        return $this->hasMany(WorkHoursSummary::className(), ['member_id' => 'member_id'])
            ->orderBy(['wp_seq' => SORT_ASC]);
    }

    /**
     * @param  string $catg Compliance credential category
     * @return ActiveQuery
     */
    public function getCredentials($catg = null)
    {
        $query = $this->hasMany(CurrentMemberCredential::className(), ['member_id' => 'member_id']);
        if (isset($catg))
            $query->onCondition(['catg' => $catg]);
        return $query;
    }

    public function getCurrentCredential($id)
    {
        return $this->hasOne(CurrentMemberCredential::className(), ['member_id' => 'member_id'])->onCondition(['credential_id' =>$id]);
    }

    /**
     * Serves as a getter for CurrentMemberCredentials::expiredCount
     */
    public function getExpiredCount()
    {
        return $this->hasMany(CurrentMemberCredential::className(), ['member_id' => 'member_id'])
            ->andOnCondition(['<', 'expire_dt', $this->getToday()->getMySqlDate()])
            ->count()
            ;
    }

    /**
     * @return ActiveQuery
     */
    public function getEmployer()
    {
    	return $this->hasOne(CurrentEmployment::className(), ['member_id' => 'member_id']);
    }

    /**
     * Returns current employer iff still actively employed
     *
     * @return ActiveQuery
     */
    public function getEmployerActive()
    {
        return $this->hasOne(Employment::className(), ['member_id' => 'member_id'])
            ->andOnCondition('end_dt is NULL')
            ;
    }

    /**
     * @return ActiveQuery
     */
    public function getDuesAllocations()
    {
        return $this->hasMany(MemberAllocation::className(), ['member_id' => 'member_id'])
            ->andOnCondition(['fee_type' => FeeType::TYPE_DUES])
            ;
    }

    /**
     * Adds a journal note to this member
     * 
     * @param Note $note
     * @throws BadMethodCallException
     * @return boolean
     */
    public function addNote($note)
    {
    	if (!($note instanceof Note))
    		throw new BadMethodCallException('Not an instance of MemberNote');
    	$note->member_id = $this->member_id;
        /** @noinspection PhpUndefinedMethodInspection */
   		$image = $note->uploadImage();
   		if ($note->save()) {
   			if ($image !== false) {
   				$path = $note->imagePath;
                /* @var $image UploadedFile */
   				$image->saveAs($path);
   			}
   			return true;
   		}
   		return false;
    }
    
    /**
     * @return ActiveQuery
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

    /**
     * When $last_nm_1st = true format name includes middle inits and suffix. Otherwise, the format
     * is simply <first_nm last_nm>
     *
     * @param bool $last_nm_1st
     * @return string
     */
    public function getFullName($last_nm_1st = true)
    {
        if ($last_nm_1st)
            $full_nm = $this->last_nm . ', ' . $this->first_nm .
                   (isset($this->middle_inits) ? ' ' . $this->middle_inits : '') .
                   (isset($this->suffix) ? ', ' .$this->suffix : '') .
                   (isset($this->nick_nm) ? ' `' . $this->nick_nm . '`' : '')
            ;
        else
            $full_nm = $this->first_nm . ' ' . $this->last_nm;
    	if (isset($this->currentStatus) && ($this->currentStatus->member_status == Status::STUB))
    		$full_nm .= ' [Stub]';
    	return $full_nm;
    }

    public function getAddressTexts()
    {
    	$texts = [];
    	foreach ($this->addresses as $address) {
    		$texts[] = $address->getAddressText(true);
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }

    public function getDefaultPhone()
    {
        return $this->hasOne(Phone::className(), ['member_id' => 'member_id'])
                    ->via('phoneDefault');
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

        if ($this->local_pac == OptionHelper::TF_TRUE)
    		$texts[] = isset($this->ncfs_id) ? "Local: Yes [NCFS ID: $this->ncfs_id]" : 'Local: Yes';
        elseif ($this->local_pac == OptionHelper::TF_DECLINED)
            $texts[] = 'Local: Declined';

    	if ($this->hq_pac == OptionHelper::TF_TRUE)
    		$texts[] = 'HQ: Yes';
        elseif ($this->hq_pac == OptionHelper::TF_DECLINED)
            $texts[] = 'HQ: Declined';

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
     * @return string | NULL
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
     * @return false|UploadedFile the uploaded image instance
     * @throws \yii\base\Exception
     */
    public function uploadImage() 
    {
        $image = UploadedFile::getInstance($this, 'photo_file');
        if (empty($image)) {
        	return false;
        }
        
        // generate a unique file name for storage
        $parts = explode(".", $image->name);
        $ext = end($parts);
        if (strtolower($ext) =='jpeg')
            $ext = 'jpg';
        $this->photo_id = Yii::$app->security->generateRandomString(16).".$ext";
 
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
     * Starting dues paid thru date is based on the application date, but can be overridden by current
     * date.  When on or prior to the 20th, the starting paid thru is the end of the previous month.
     * Otherwise, it is the end of the current month.
     *
     * @param bool $use_current If true, use today's date instead of application date
     * @return OpDate
     * @throws \Exception
     */
    public function getDuesStartDt($use_current = false)
    {
    	$dt = $use_current ? $this->getToday() : clone $this->getApplicationDtObject();
    	if ($dt->getDay() > self::CUTOFF_DAY)
    		$dt->modify('+1 month');
    	$dt->setDate($dt->getYear(), $dt->getMonth(), 1);
    	$dt->modify('-1 day');
    	return $dt;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getDuesBalance()
    {
        // Do NOT use DuesBalance VIEW; performs poorly
        // return $this->hasOne(DuesBalance::className(), ['member_id' => 'member_id']);
        $sql = <<<SQL
            SELECT 
              Me.member_id,
              CASE 
                WHEN FC.lob_cd IS NULL THEN 0.00 
                WHEN MS.member_status = 'O' THEN 0.00
                ELSE SUM(FC.rate) 
              END AS balance_amt
            FROM Members AS Me
              JOIN MemberStatuses AS MS ON MS.member_id = Me.member_id
                                         AND MS.effective_dt = (SELECT MAX(effective_dt) FROM MemberStatuses WHERE member_id = Me.member_id)
              LEFT OUTER JOIN MemberClasses AS MC ON MC.member_id = Me.member_id
                                        AND MC.end_dt IS NULL
                LEFT OUTER JOIN FeeCalendar AS FC ON FC.lob_cd = MS.lob_cd
                                                   AND FC.rate_class = MC.rate_class
                                                   AND FC.end_dt BETWEEN DATE_ADD(Me.dues_paid_thru_dt, INTERVAL 1 DAY) AND LAST_DAY(CURDATE())   
            WHERE Me.member_id = :member_id
            GROUP BY Me.member_id 
            ;
SQL;
        $cmd = Yii::$app->db->createCommand($sql);
        $cmd->bindValues([
            ':member_id' => $this->member_id,
        ]);
        $result = $cmd->queryOne();
        return $result['balance_amt'];
    }

    /**
     * @return ActiveQuery
     */
    public function getFeeBalances()
    {
        return $this->hasMany(FeeBalance::className(), ['member_id' => 'member_id']);
    }

    public function getCcgBalanceCount()
    {
        return $this->hasMany(FeeBalance::className(), ['member_id' => 'member_id'])
            ->andOnCondition(['fee_type' => FeeType::TYPE_CC])
            ->count()
        ;
    }

    public function getTotalFeeBalance()
    {
        return $this->hasMany(FeeBalance::className(), ['member_id' => 'member_id'])
            ->sum('balance_amt')
        ;
    }

    /**
     * @return float|string
     * @throws Exception
     */
    public function getAllBalance()
    {
        // Do NOT use AllBalance VIEW; performs poorly
        // return $this->hasOne(AllBalance::className(), ['member_id' => 'member_id']);
        $balance = 'Pending';
        if (isset($this->currentStatus) && isset($this->currentClass)) {
            $balance = 0.00;
            if (!($this->currentStatus->member_status == Status::OUTOFSTATE)) {
                $balance = bcsub($this->getDuesBalance(), $this->overage, 2);
                $fee_balance = $this->totalFeeBalance;
                if (!is_null($fee_balance))
                    $balance = bcadd($balance, $fee_balance, 2);
            }
        }
        return $balance;
    }

    /**
     * An active member is "in application" if his initiation date is null for a new member,
     * or less than the application date if he has been assessed a new APF after reinstatement
     * 
     * @return boolean
     */
    public function isInApplication()
    {
    	$result = false;
    	if (isset($this->currentStatus)) {
    		if ($this->currentStatus->member_status == 'N') {
    			$result = true;
    		} elseif ($this->currentStatus->member_status == 'A') {
    			$result = is_null($this->init_dt);
    			/*
	    		$application_dt = $this->getApplicationDtObject();
    			if (isset($this->init_dt)) {
	    			$init_dt = (new OpDate)->setFromMySql($this->init_dt);
	    			$result = (OpDate::dateDiff($application_dt, $init_dt) < 0);
    			} else { // no init_dt
    				$result = true;
    			}
    			*/
    		} elseif (isset($this->reinstateStaged))
                $result = ($this->reinstateStaged->reinstate_type == ReinstateForm::TYPE_APF) || ($this->reinstateStaged->reinstate_type == ReinstateForm::TYPE_WAIVE);

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
     * @return ActiveQuery
     */
    public function getAssessments()
    {
    	return $this->hasMany(Assessment::classname(), ['member_id' => 'member_id']);	
    }

    /**
     * @param Assessment $class
     * @return bool
     * @throws \yii\base\Exception
     */
    public function addAssessment(Assessment $class)
    {
    	$class->member_id = $this->member_id;
    	if ($class->validate())
    	    return $class->save();
    	throw new \yii\base\Exception('Invalid Assessment.  Errors: ' . print_r($class->errors, true));
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCurrentApf()
    {
    	return $this->hasOne(ApfAssessment::className(), ['member_id' => 'member_id'])
    		 		->andOnCondition(['fee_type' => FeeType::TYPE_INIT])
    		 		->andOnCondition(['assessment_dt' => $this->application_dt])
    	;
    }

    public function getLastDuesReceipt()
    {
    	return $this->hasOne(LastDuesReceipt::className(), ['member_id' => 'member_id']);
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
    			throw new \Exception("Dues paid thru date is not set for member: $this->member_id");
    		$result = $this->isOlderThanCutoff(self::MONTHS_DELINQUENT);
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
    			throw new \Exception("Dues paid thru date is not set for member: $this->member_id");
    		$result = $this->isOlderThanCutoff(self::MONTHS_GRACE_PERIOD);
    	}
    	return $result;	
    }

    /**
     * @return OpDate
     */
    public function getDuesPaidThruDtObject()
    {
    	if (!isset($this->_dues_paid_thru_dt))
    		$this->_dues_paid_thru_dt = (new OpDate)->setFromMySql($this->dues_paid_thru_dt);
    	return $this->_dues_paid_thru_dt;
    }

    /**
     * Uses Standing class to estimate the dues owed for a member
     *
     * @param bool $apf_only
     * @return false|float|string|null
     */
    public function estimateDuesOwed($apf_only = false)
    {
        if (isset($this->reinstateStaged))
            return $this->reinstateStaged->dues_owed_amt;
        $standing = $this->getStanding($apf_only);
        return $standing->getDuesBalance();
    }

    public function getRecurCcAuth()
    {
        return $this->hasOne(Document::className(), ['member_id' => 'member_id'])
                    ->andOnCondition(['doc_type' => DocumentType::TYPE_RECURRING_CCAUTH])
        ;
    }

    /**
     * @param $months
     * @return bool
     * @throws \Exception
     */
    protected function isOlderThanCutoff($months)
    {
    	$cutoff = $this->getToday();
    	$cutoff->modify('-' . $months . ' month');
    	$cutoff->setToMonthEnd();
    	return (OpDate::dateDiff($this->getDuesPaidThruDtObject(), $cutoff) > 0);
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
     *
     * @return bool
     */
    public function isCasLevel2()
    {
        /* @var CurrentMemberCredential $cas */
        $cas = $this->getCurrentCredential(Credential::CAS_LEVEL_2)->one();
        if (isset($cas)) {
            if ($cas->expire_dt >= $this->getToday()->getMySqlDate())
                return true;
            // If CAS level 2 is expired, but there is no Level 1 credential, expired level 2 will display
            if (!($this->getCurrentCredential(Credential::CAS_LEVEL_1)->one()))
                return true;
        }
        return false;
    }

    /**
     * @return ActiveQuery
     */
    public function getMatchingOverage()
    {
        return $this->hasOne(OverageHistory::className(), ['member_id' => 'member_id', 'dues_paid_thru_dt' => 'dues_paid_thru_dt']);
    }

    /**
     * @param OverageHistory $history
     * @return bool
     */
    public function addOverageHistory(OverageHistory $history)
    {
        $history->member_id = $this->member_id;
        $history->dues_paid_thru_dt = $this->dues_paid_thru_dt;
        $history->overage = $this->overage;
        return $history->save();
    }

    /**
     * Return an array of WorkProcess active records that are pertinent to the member's current trade
     *
     * @return WorkProcess[]|Size[]|array|ActiveRecord[]
     */
    public function getProcesses()
    {
        if (!(isset($this->_processes)))
            $this->_processes = WorkProcess::find()->where([
                'lob_cd' => $this->currentStatus->lob_cd,
            ])->indexBy('id')->orderBy('seq')->all();

        return $this->_processes;
    }

    /**
     * @return array
     */
    public function getProcOptions()
    {
        $procs = $this->processes;
        $options = [];
        foreach ($procs as $proc)
            $options[$proc->seq] = $proc->descrip;
        return $options;
    }

    /**
     * Override this function when testing with fixed date
     * 
     * @return OpDate
     */
    public function getToday()
    {
    	return new OpDate();
    }

    /**
     * @param bool $apf_only
     * @return Standing
     */
    private function getStanding($apf_only = false)
    {
        if(!(isset($this->standing)))
            $this->standing = new Standing([
                'member' => $this,
                'apf_only' => $apf_only,
            ]);
        return $this->standing;
    }

}
