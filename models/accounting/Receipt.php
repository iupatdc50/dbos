<?php

namespace app\models\accounting;

use Yii;
use yii\base\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use app\helpers\OptionHelper;

use app\models\user\User;
use app\models\value\Lob;
use app\modules\admin\models\FeeType;
use app\components\utilities\OpDate;

/**
 * This is the model class for table "Receipts".
 *
 * @property integer $id
 * @property string $payor_nm
 * @property string $payment_method
 * @property string $tracking_nbr
 * @property string $payor_type
 * @property string $received_amt
 * @property string $received_dt
 * @property string $unallocated_amt
 * @property string $helper_dues
 * @property string $helper_hrs
 * @property integer $created_at
 * @property integer $created_by
 * @property string $remarks
 * @property string $lob_cd
 * @property string $acct_month
 * @property string $void [enum('T', 'F')]
 * @property int $updated_by [int(11)]
 * @property int $updated_at [int(11)]
 *
 * @property array $methodOptions
 * @property string $filePath
 * @property AllocatedMember[] $members
 * @property ReceiptFeeType[] $feeTypes
 * @property ReceiptAllocSumm[] $allocSumms
 * @property AssessmentAllocation[] $assessmentAllocations
 * @property DuesAllocation[] $duesAllocations
 * @property User $createdBy
 * @property User $updatedBy
 * @property string $totalAllocation [decimal(7,2)]
 * @property string $outOfBalance [decimal(7,2)]
 * @property OpDate $today
 */
class Receipt extends ActiveRecord
{
	CONST SCENARIO_CONFIG = 'config';
    CONST SCENARIO_CREATE = 'create';

	CONST METHOD_CASH = '1';
	CONST METHOD_CHECK = '2';
	CONST METHOD_CREDIT = '3';
	CONST METHOD_WAIVER = '4';

	CONST PAYOR_CONTRACTOR = 'C';
	CONST PAYOR_MEMBER = 'M';
	CONST PAYOR_OTHER = 'O';

	protected $_validationRules = [];
	protected $_labels = [];
	protected $_remit_filter;

//	public $lob_cd;
	public $fee_types = [];
    /**
     * @var bool    True means to generate allocation rows for each employee
     */
	public $populate;
	
	/**
	 * @var mixed	Stages spreadsheet to be uploaded
	 */
	public $xlsx_file;
	/**
	 * @var string 	Noise name generated for uploaded spreadsheet
	 */
	public $xlsx_name;
	
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Receipts';
    }

    public static function instantiate($row)
    {
        if ($row['payor_type'] == self::PAYOR_CONTRACTOR)
            return new ReceiptContractor();
        elseif ($row['payor_type'] == self::PAYOR_MEMBER)
            return new ReceiptMember();
        elseif ($row['payor_type'] == self::PAYOR_OTHER)
            return new ReceiptOther();
        return new self;
    }

    public static function getAllowedMethods()
    {
    	return [
    			self::METHOD_CASH,
    			self::METHOD_CHECK,
    			self::METHOD_CREDIT,
                self::METHOD_WAIVER,
    	];
    }
    
    public static function getAllowedPayors()
    {
    	return [
    			self::PAYOR_CONTRACTOR,
    			self::PAYOR_MEMBER,
    			self::PAYOR_OTHER,
    	];
    }
    
    public static function getPayorOptions()
    {
    	return [
    			self::PAYOR_CONTRACTOR => 'Contractor',
    			self::PAYOR_MEMBER => 'Member',
    			self::PAYOR_OTHER => 'Other',
    	];
    }

    public function behaviors()
	{
		return [
				['class' => TimestampBehavior::className()],
				['class' => BlameableBehavior::className()],
		];
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $common_rules = [
            [['payor_nm'], 'string', 'max' => 100],
        	[['payor_nm', 'helper_hrs'], 'default', 'value' => null],
        	[['payment_method', 'received_dt', 'received_amt', 'acct_month'], 'required'],
        	[['payment_method'], 'in', 'range' => self::getAllowedMethods()],
        	[['payor_type'], 'in', 'range' => self::getAllowedPayors()],
        	[['received_dt'], 'date', 'format' => 'php:Y-m-d'],
        	[['received_dt'], 'validateReceivedDt'],
        	[['received_amt', 'unallocated_amt'], 'number'],
        	[['unallocated_amt', 'helper_dues'], 'default', 'value' => 0.00],
            ['helper_hrs', 'required', 'when' => function($model) {
            	return $model->helper_dues > 0.00;
            }, 'whenClient' => "function (attribute, value) {
            	return $('#helperdues').val() > 0.00;
    		}"],
        	[['tracking_nbr'], 'string', 'max' => 20],
        	[['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
        	[['remarks', 'fee_types', 'populate', 'xlsx_file'], 'safe'],
        	['fee_types', 'required', 'when' => function($model) {
                return !($model->helper_dues > 0.00);
            }, 'whenClient' => "function (attribute, value) {
            	return !($('#helperdues').val() > 0.00);
    		}", 'on' => self::SCENARIO_CREATE, 'message' => 'Please select at least one Fee Type or enter Helper Dues'],
        	['lob_cd', 'required', 'on'  => self::SCENARIO_CONFIG],
        ];
        return array_merge($this->_validationRules, $common_rules);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $common_labels = [
            'id' => 'Receipt Nbr',
        	'payor_nm' => 'Payor',
            'payment_method' => 'Pay Method',
            'payor_type' => 'Payor Type',
            'received_dt' => 'Received',
            'received_amt' => 'Amount',
            'unallocated_amt' => 'Unallocated',
            'helper_dues' => 'Helper Dues',
            'helper_hrs' => 'Hours',
        	'created_at' => 'Created At',
            'created_by' => 'Entered by',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated by',
        	'remarks' => 'Remarks',
        	'feeTypeTexts' => 'Fee Types',
        	'xlsx_file' => 'Import From Spreadsheet',
            'lob_cd' => 'Trade',
        	'acct_month' => 'Acct Month',
            'populate' => 'Prebuild Employees',
        ];
        return array_merge($this->_labels, $common_labels);
    }

    public function validateReceivedDt($attribute, /** @noinspection PhpUnusedParameterInspection */
                                       $params)
    {
    	$dt = (new OpDate)->setFromMySql($this->$attribute);
    	if (OpDate::dateDiff($this->today, $dt) > 0)
    	    $this->addError($attribute, 'Received date cannot be future');
    }

    public function beforeSave($insert)
	{
		// default does not appear to be working here or at the DB level
		if (parent::beforeSave($insert)) {
			if ($insert) {
				if (empty($this->unallocated_amt))
					$this->unallocated_amt = 0.00;
				if (empty($this->helper_dues))
					$this->helper_dues = 0.00;
			}
			return true;
		}
		return false;
	}

    /**
     * Closes outstanding in progress credit card transaction
     */
    public function closeInProgressTrans()
    {
        // stub
    }

    /**
     * @return ActiveQuery
     */
    public function getMembers()
    {
        return $this->hasMany(AllocatedMember::className(), ['receipt_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getAcctMonthText()
    {
    	return OptionHelper::getPrettyMonthYear($this->acct_month);
    }
    
    public function getAcctMonthOptions(OpDate $base_dt = null)
    {
    	if (!isset($base_dt)) {
    		$base_dt = new OpDate();
    		 if (isset($this->received_dt))
    		     $base_dt->setFromMySql($this->received_dt);
    	}
    	return OpDate::getMonthsList($base_dt, 2);
    }

    public function getMethodOptions()
    {
    	return [
    			self::METHOD_CASH => 'Cash',
    			self::METHOD_CHECK => 'Check',
    			self::METHOD_CREDIT => 'Credit',
                self::METHOD_WAIVER => 'Waiver',
    	];
    }
    
    public function getMethodText($code = null)
    {
    	$method = isset($code) ? $code : $this->payment_method;
    	$options = $this->methodOptions;
    	return isset($options[$method]) ? $options[$method] : "Unknown Payment Method `{$method}`";
    }
    
    public function getPayorText($code = null)
    {
    	$payor = isset($code) ? $code : $this->payor_type;
    	$options = self::getPayorOptions();
    	return isset($options[$payor]) ? $options[$payor] : "Unknown Payor Type `{$payor}`";
    }

    /**
     * @return string Lower case qualifier for use in routing URLs
     */
    public function getUrlQual()
    {
        $type = $this->payor_type;
        $options = self::getPayorOptions();
        return isset($options[$type]) ? strtolower($options[$type]) : "Unknown Payor Type `{$type}`";
    }

    /**
     * @return array
     * @throws Yii\base\InvalidConfigException
     */
    public function getFeeOptions()
    {
    	if(!isset($this->_remit_filter))
    		throw new yii\base\InvalidConfigException('Unknown remittable filter field');
        if(!isset($this->lob_cd))
            throw new yii\base\InvalidConfigException('Missing LOB field');
    	return ArrayHelper::map(TradeFeeType::find()->where(['lob_cd' => $this->lob_cd, $this->_remit_filter => 'T'])->orderBy('seq')->all(), 'fee_type', 'descrip');
    }

    /**
     * Uses an SQL view to determine unique fee types currently on this receipt
     *
     * @return ActiveQuery
     */
    public function getFeeTypes()
    {
    	return $this->hasMany(ReceiptFeeType::className(), ['receipt_id' => 'id']);
    }
    
    public function getFeeTypesArray()
    {
    	return ArrayHelper::getColumn($this->feeTypes, 'fee_type');
    }
    
    public function getFeeTypeTexts()
    {
    	$texts = [];
    	foreach ($this->feeTypes as $feeType) {
    		$texts[] = $feeType->feeType->extDescrip;
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    public function getAllocatedMembers()
    {
    	return $this->hasMany(AllocatedMember::className(), ['receipt_id' => 'id']);
    }

    /**
     * Determine whether allocations exist.  Assume none if no allocated members
     *
     * @return int      Number of allocated members
     */
    public function getAllocatedCount()
    {
        return $this->hasMany(AllocatedMember::className(), ['receipt_id' => 'id'])->count('member_id');
    }

    public function getAllocSumms()
    {
    	return $this->hasMany(ReceiptAllocSumm::className(), ['receipt_id' => 'id']);
    }

    /**
     * @return number
     */
    public function getTotalAllocation()
    {
    	return $this->hasMany(BaseAllocation::className(), ['alloc_memb_id' => 'id'])
    				->andOnCondition(['<>', 'fee_type', FeeType::TYPE_HOURS])
    				->via('allocatedMembers')
    				->sum('allocation_amt');
    }
    
    public function getDuesAllocations()
    {
    	return $this->hasMany(DuesAllocation::className(), ['alloc_memb_id' => 'id'])
    				->andOnCondition(['fee_type' => FeeType::TYPE_DUES])
    			    ->via('allocatedMembers')
    	;
    }
    
    public function getOutOfBalance()
    {
        /** @noinspection PhpWrongStringConcatenationInspection */
        $alloc = $this->totalAllocation + $this->unallocated_amt + $this->helper_dues;
        // Can't use standard substract on FP numbers
        return bcsub($this->received_amt, $alloc, 2);
    }
    
    public function getAssessmentAllocations()
    {
    	return $this->hasMany(AssessmentAllocation::className(), ['alloc_memb_id' => 'id'])
    		->andOnCondition(['<>', 'fee_type', FeeType::TYPE_DUES])
    		->via('allocatedMembers')
    	;
    }
    
    /**
     * Fetch spreadsheet file name with complete path (FQDN)
     * 
     * @return null|string
     */
    public function getFilePath()
    {
    	$path = Yii::getAlias('@webroot') . Yii::$app->params['uploadDir'];
    	return isset($this->xlsx_name) ? $path . $this->xlsx_name : null;
    }

    /**
     * Process upload of spreadsheet
     *
     * @return mixed the uploaded spreadsheet
     * @throws Exception
     */
    public function uploadFile()
    {
    	$file = UploadedFile::getInstance($this, 'xlsx_file');
    	if (empty($file))
    		return false;
    	
        // generate a unique file name for storage
        $name_parts = explode(".", $file->name);
        $ext = end($name_parts);
        $this->xlsx_name = Yii::$app->security->generateRandomString(16).".{$ext}";
    	
        return $file;
    	
    }

    /**
     * Process deletion of uploaded spreadsheet
     *
     * @return boolean the status of deletion
     */
    public function deleteUploadedFile()
    {
    	$file = $this->filePath;
    
    	// check if file exists on server
    	if (empty($file) || !file_exists($file)) {
    		return false;
    	}
    
    	// check if uploaded file can be deleted on server
    	if (!unlink($file)) {
    		return false;
    	}
    
    	// if deletion successful, reset your file attributes
    	$this->xlsx_name = null;
    
    	return true;
    }

    /**
     * @param bool $forPrint If true, excludes certain attributes from printable receipt
     * @return array
     */
    public function getCustomAttributes(/** @noinspection PhpUnusedParameterInspection */
        $forPrint = false)
    {
    	return [];
    }

    /**
     * @return ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    public function getLobOptions()
    {
    	return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
    }

    /**
     * Calls a stored procedure to make a hold copy of a receipt to be updated.  The procedure checks for an
     * already existing hold copy.  If there are any member status records that are connected to the
     * receipt's allocation, these are backed up, also.
     *
     * @param $undo_id receipt id
     * @throws \yii\db\Exception
     */
    public function makeUndo($undo_id)
    {
        $db = Yii::$app->db;
        $db->createCommand("CALL MakeUndoReceipt (:undo_id)", [':undo_id' => $undo_id])->execute();
    }

    /**
     * Calls a stored to procedure to restore the original receipt that was in the process of updating.
     * It also restores any status records connnected to the receipt.
     *
     * @param $undo_id receipt id
     * @throws \yii\db\Exception
     */
    public function cancelUpdate($undo_id)
    {
        $db = Yii::$app->db;
        $db->createCommand("CALL CancelReceiptUpdate (:undo_id)", [':undo_id' => $undo_id])->execute();
    }

    /**
     * @param $id
     * @throws \yii\db\Exception
     */
    public function cleanup($id)
    {
        $db = Yii::$app->db;
        $db->createCommand("CALL RemoveUndoReceipt (:undo_id)", [':undo_id' => $id])->execute();
    }

    public function isUpdating()
    {
        $result = UndoReceipt::findOne($this->id);
        return (!is_null($result));
    }

    public function dependenciesUpdated()
    {
        $this->markAttributeDirty('updated_by');
    }
    
    /**
     * Override this function when testing with fixed date
     *
     * @return OpDate
     */
    protected function getToday()
    {
    	return new OpDate();
    }
    
}
