<?php

namespace app\models\accounting;

use Yii;
use yii\helpers\ArrayHelper;

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
        	['fee_types', 'safe'],
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
    			self::PAYOR_CONTRACTOR,
    			self::PAYOR_MEMBER,
    			self::PAYOR_OTHER,
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
    
    
}
