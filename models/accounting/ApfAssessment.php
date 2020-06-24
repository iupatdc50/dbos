<?php

namespace app\models\accounting;

use app\models\member\Member;
use app\models\member\ReinstateForm;
use app\modules\admin\models\FeeType;
use Yii;
use yii\base\Exception;

/**
 * Model class where fee_type = 'IN'
 * 
 * @property string $months
 * 
 */
class ApfAssessment extends Assessment
{

    public static function find()
    {
        return new AssessmentQuery(get_called_class(), ['type' => FeeType::TYPE_INIT, 'tableName' => self::tableName()]);
    }

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		/*
		$this->_validationRules = [
				[['months'], 'number'],
		];
		*/
		return parent::rules();
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		/*
		$this->_labels = [
				'months' => 'Dues Months',
		];
		*/
		return parent::attributeLabels();
	}


    /**
     * @param ReinstateForm $model
     * @param Member $member
     * @return bool
     * @throws Exception
     */
	public function makeFromReinstate(ReinstateForm $model, Member $member)
    {
        if (!parent::makeFromReinstate($model, $member))
            return false;
        if ($model->type == ReinstateForm::TYPE_APF) {
            $purpose = [];
            $assessment_amt = 0.00;
            foreach ($model->assessments_a as $assessment) {
                $fee = $model->getFee($assessment);
                $assessment_amt += $fee['amt'];
                $purpose[] = $fee['descrip'] . $fee['amt'];
            }
            $this->fee_type = FeeType::TYPE_INIT;
            $this->assessment_amt = $assessment_amt;
            $this->months = 0;
            $this->purpose = 'Reinstatement APF; ' . implode(', ', $purpose);
            if ($member->addAssessment($this)) {
                $member->application_dt = $this->assessment_dt;
                if ($member->save())
                    return true;
            }
        }
        Yii::error('*** AA010 Unable to save assessment.  Error: ' . print_r($this->errors, true));
        return false;
    }

}