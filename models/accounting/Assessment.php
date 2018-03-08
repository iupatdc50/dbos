<?php

namespace app\models\accounting;

use app\modules\admin\models\FeeType;
use yii\helpers\ArrayHelper;
use app\models\member\Member;
use app\models\user\User;

/**
 * This is the model class for table "Assessments".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $fee_type
 * @property string $assessment_dt
 * @property string $assessment_amt
 * @property string $purpose
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Member $member
 * @property FeeType $feeType
 * @property User $createdBy
 * @property string $months [decimal(7,2)]
 */
class Assessment extends \yii\db\ActiveRecord
{

	protected $_validationRules = [];
	protected $_labels = [];
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Assessments';
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
            [['member_id', 'fee_type', 'assessment_amt'], 'required'],
        	['assessment_dt', 'date', 'format' => 'php:Y-m-d'],
            [['assessment_amt', 'months'], 'number'],
            [['purpose'], 'string'],
            [['created_at', 'created_by'], 'integer'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
            [['fee_type'], 'exist', 'targetClass' => FeeType::classname()],
        	['months', 'required', 'when' => function($model) {
            	return ($model->fee_type == 'IN');
            }, 'whenClient' => "function (attribute, value) {
            	return $('#feetype').val() == 'IN';
    		}"],	
        ];
       return array_merge($this->_validationRules, $common_rules);
     }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $common_labels = [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'fee_type' => 'Fee Type',
            'assessment_amt' => 'Assessment',
            'purpose' => 'Purpose',
        	'months' => 'Dues Months',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
        return array_merge($this->_labels, $common_labels);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTotalAllocated()
    {
    	return $this->hasMany(AssessmentAllocation::className(), ['assessment_id' => 'id'])->sum('allocation_amt');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAllocatedPayments()
    {
    	return $this->hasMany(AssessmentAllocation::className(), ['assessment_id' => 'id']);
    }
    
    public function getBalance()
    {
        // Can't use standard substract on FP numbers
    	return bcsub($this->assessment_amt, $this->totalAllocated);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
    }

    public function getFeeOptions()
    {
    	return ArrayHelper::map(FeeType::find()->where(['is_assess' => 'T'])->orderBy('descrip')->all(), 'fee_type', 'descrip');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    
    
    
}
