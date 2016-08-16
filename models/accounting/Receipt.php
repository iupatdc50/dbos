<?php

namespace app\models\accounting;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "Receipts".
 *
 * @property integer $id
 * @property string $payor_nm
 * @property string $payment_method
 * @property string $tracking_nbr
 * @property string $payor_type
 * @property string $received_dt
 * @property string $received_amt
 * @property string $unallocated_amt
 * @property integer $created_at
 * @property integer $created_by
 * @property string $remarks
 *
 * @property AllocatedMember[] $members
 * @property User $createdBy
 */
class Receipt extends \yii\db\ActiveRecord
{
	
	CONST METHOD_CASH = '1';
	CONST METHOD_CHECK = '2';
	CONST METHOD_CREDIT = '3';

	CONST PAYOR_CONTRACTOR = 'C';
	CONST PAYOR_MEMBER = 'M';
	CONST PAYOR_OTHER = 'O';
	
	protected $_validationRules = [];
	protected $_labels = [];
	protected $_remit_filter;
	
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
        	[['payor_nm'], 'default', 'value' => null],
        	[['payment_method', 'payor_type', 'received_dt', 'received_amt'], 'required'],
        	[['payment_method'], 'in', 'range' => self::getAllowedMethods()],
        	[['payor_type'], 'in', 'range' => self::getAllowedPayors()],
            [['received_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['received_amt', 'unallocated_amt'], 'number'],
        	[['unallocated_amt'], 'default', 'value' => 0.00],
        	[['tracking_nbr'], 'string', 'max' => 20],
        	[['created_at', 'created_by'], 'integer'],
        	[['fee_types', 'xlsx_file'], 'safe'],
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
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        	'remarks' => 'Remarks',
        	'xlsx_file' => 'Import From Spreadsheet',
        ];
        return array_merge($this->_labels, $common_labels);
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
    
    public function getPayorOptions()
    {
    	return [
    			self::PAYOR_CONTRACTOR => 'Contractor',
    			self::PAYOR_MEMBER => 'Member',
    			self::PAYOR_OTHER => 'Other',
    	];
    }
    
    public function getPayorText($code)
    {
    	$options = $this->payorOptions;
    	return isset($options[$code]) ? $options[$code] : "Unknown Payor Type `{$code}`";
    }
    
    public function getFeeOptions($lob_cd)
    {
    	if(!isset($this->_remit_filter))
    		throw new yii\base\InvalidConfigException('Unknown remittable filter field');
    	return ArrayHelper::map(TradeFeeType::find()->where(['lob_cd' => $lob_cd, $this->_remit_filter => 'T'])->orderBy('descrip')->all(), 'fee_type', 'descrip');
    }
    
    public function getAllocatedMembers()
    {
    	return $this->hasMany(AllocatedMember::className(), ['receipt_id' => 'id']);
    }
    
    public function getTotalAllocation()
    {
    	return $this->hasMany(BaseAllocation::className(), ['alloc_memb_id' => 'id'])
    				->via('allocatedMembers')
    				->sum('allocation_amt');
    }
    
    public function getOutOfBalance()
    {
    	return $this->received_amt - $this->totalAllocation;
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
    
}
