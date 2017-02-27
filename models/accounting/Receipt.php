<?php

namespace app\models\accounting;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use app\models\user\User;
use app\models\value\Lob;
use app\modules\admin\models\FeeType;

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
 * @property string $received_amt
 * @property string $unallocated_amt
 * @property string $helper_dues
 * @property string $helper_hrs
 * @property integer $created_at
 * @property integer $created_by
 * @property string $remarks
 *
 * @property AllocatedMember[] $members
 * @property ReceiptFeeType[] $feeTypes
 * @property ReceiptAllocSumm[] $allocSumms
 * @property User $createdBy
 */
class Receipt extends \yii\db\ActiveRecord
{
	CONST SCENARIO_CONFIG = 'config';
	CONST SCENARIO_CREATE = 'create';
	
	CONST METHOD_CASH = '1';
	CONST METHOD_CHECK = '2';
	CONST METHOD_CREDIT = '3';

	CONST PAYOR_CONTRACTOR = 'C';
	CONST PAYOR_MEMBER = 'M';
	CONST PAYOR_OTHER = 'O';
	
	protected $_validationRules = [];
	protected $_labels = [];
	protected $_remit_filter;
	protected $_customAttributes = [];
	
	public $lob_cd;
	public $fee_types = [];
	
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
    
    public static function getAllowedMethods()
    {
    	return [
    			self::METHOD_CASH,
    			self::METHOD_CHECK,
    			self::METHOD_CREDIT,
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
				['class' => \yii\behaviors\TimestampBehavior::className(), 'updatedAtAttribute' => false],
				['class' => \yii\behaviors\BlameableBehavior::className(), 'updatedByAttribute' => false],
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
        	[['payment_method', 'payor_type', 'received_dt', 'received_amt'], 'required'],
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
        	[['created_at', 'created_by'], 'integer'],
        	[['remarks', 'fee_types', 'xlsx_file'], 'safe'],
        	['fee_types', 'required', 'on'  => self::SCENARIO_CREATE, 'message' => 'Please select at least one Fee Type'],
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
            'payment_method' => 'Payment Method',
            'payor_type' => 'Payor Type',
            'received_dt' => 'Received Date',
            'received_amt' => 'Amount',
            'unallocated_amt' => 'Unallocated',
            'helper_dues' => 'Helper Dues',
            'helper_hrs' => 'Hours',
        	'created_at' => 'Created At',
            'created_by' => 'Entered by',
        	'remarks' => 'Remarks',
        	'feeTypeTexts' => 'Fee Types',
        	'xlsx_file' => 'Import From Spreadsheet',
            'lob_cd' => 'Union',
        ];
        return array_merge($this->_labels, $common_labels);
    }
    
    public function validateReceivedDt($attribute, $params)
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
     * @return \yii\db\ActiveQuery
     */
    public function getMembers()
    {
        return $this->hasMany(AllocatedMember::className(), ['receipt_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getMethodOptions()
    {
    	return [
    			self::METHOD_CASH => 'Cash',
    			self::METHOD_CHECK => 'Check',
    			self::METHOD_CREDIT => 'Credit',
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
    
    public function getFeeOptions($lob_cd)
    {
    	if(!isset($this->_remit_filter))
    		throw new yii\base\InvalidConfigException('Unknown remittable filter field');
    	return ArrayHelper::map(TradeFeeType::find()->where(['lob_cd' => $lob_cd, $this->_remit_filter => 'T'])->orderBy('descrip')->all(), 'fee_type', 'descrip');
    }
    
    public function getFeeTypes()
    {
    	return $this->hasMany(ReceiptFeeType::className(), ['receipt_id' => 'id']);
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
    
    public function getAllocSumms()
    {
    	return $this->hasMany(ReceiptAllocSumm::className(), ['receipt_id' => 'id']);
    }
    
    public function getTotalAllocation()
    {
    	return $this->hasMany(BaseAllocation::className(), ['alloc_memb_id' => 'id'])
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
    	return $this->received_amt - ($this->totalAllocation + $this->unallocated_amt + $this->helper_dues);
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
     * @return <string, NULL>
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
     */
    public function uploadFile()
    {
    	$file = UploadedFile::getInstance($this, 'xlsx_file');
    	if (empty($file))
    		return false;
    	
        
        // generate a unique file name for storage
        $ext = end((explode(".", $file->name)));
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
    
    public function getCustomAttributes($forPrint = false)
    {
    	return $this->_customAttributes;
    }
    
    public function getLobOptions()
    {
    	return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
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
