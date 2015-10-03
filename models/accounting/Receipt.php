<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "Receipts".
 *
 * @property integer $id
 * @property string $payment_method
 * @property string $payor_type
 * @property string $received_dt
 * @property string $received_amt
 * @property string $unallocated_amt
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property DuesAllocation[] $duesAllocations
 * @property Member[] $members
 * @property OtherAllocation[] $otherAllocations
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
        return [
        	[['payment_method', 'payor_type', 'received_dt', 'received_amt'], 'required'],
        	[['payment_method'], 'in', 'range' => self::getAllowedMethods()],
        	[['payor_type'], 'in', 'range' => self::getAllowedPayors()],
            [['received_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['received_amt', 'unallocated_amt'], 'number'],
            [['created_at', 'created_by'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_method' => 'Payment Method',
            'payor_type' => 'Payor Type',
            'received_dt' => 'Received Date',
            'received_amt' => 'Received Amount',
            'unallocated_amt' => 'Unallocated',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDuesAllocations()
    {
        return $this->hasMany(DuesAllocation::className(), ['receipt_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMembers()
    {
        return $this->hasMany(Member::className(), ['member_id' => 'member_id'])->viaTable('DuesAllocations', ['receipt_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOtherAllocations()
    {
        return $this->hasMany(OtherAllocation::className(), ['receipt_id' => 'id']);
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
    
    public function getMethodText($code)
    {
    	$options = $this->methodOptions;
    	return isset($options[$code]) ? $options[$code] : "Unknown Payment Method `{$code}`";
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
    
}
