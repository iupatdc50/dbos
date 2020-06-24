<?php

namespace app\models\accounting;

use app\models\member\ReinstateForm;
use app\modules\admin\models\FeeType;
use http\Exception\BadMethodCallException;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
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
 * @property string $totalAllocated [decimal(7,2)]
 * @property string $balance [decimal(7,2)]
 */
class Assessment extends ActiveRecord
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

    public static function instantiate($row)
    {
        if ($row['fee_type'] == FeeType::TYPE_INIT)
            return new ApfAssessment();
        else if ($row['fee_type'] == FeeType::TYPE_REINST)
            return new ReinstateAssessment();
        return new self;
    }

    public function behaviors()
	{
		return [
				['class' => TimestampBehavior::className(), 'updatedAtAttribute' => false],
				['class' => BlameableBehavior::className(), 'updatedByAttribute' => false],
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
     * @return ActiveQuery
     */
    public function getTotalAllocated()
    {
    	return $this->hasMany(AssessmentAllocation::className(), ['assessment_id' => 'id'])->sum('allocation_amt');
    }
    
    /**
     * @return ActiveQuery
     */
    public function getAllocatedPayments()
    {
    	return $this->hasMany(AssessmentAllocation::className(), ['assessment_id' => 'id']);
    }
    
    public function getBalance()
    {
        // Can't use standard substract on FP numbers
    	return bcsub($this->assessment_amt, $this->totalAllocated, 2);
    }

    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return ActiveQuery
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
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * Generate assessment based on ReinstateForm.  Assumes that assessment_dt is set.
     *
     * @param ReinstateForm $model
     * @param Member $member
     * @return bool
     * @noinspection PhpUnusedParameterInspection
     */
    public function makeFromReinstate(ReinstateForm $model, Member $member)
    {
        if (!isset($this->assessment_dt)) {
            Yii::error('*** AS010 Missing assessment date');
            throw new BadMethodCallException('Missing assessment_dt');
        }
        return true;
        // stub
    }
    
}
