<?php


namespace app\models\accounting;


use app\models\member\Member;
use app\models\member\ReinstateForm;
use app\modules\admin\models\FeeType;
use Yii;
use yii\base\Exception;

/**
 * Class ReinstateAssessment
 *
 * Model class where fee_type = 'RN'
 *
 * @package app\models\accounting
 */
class ReinstateAssessment extends Assessment
{

    public static function find()
    {
        return new AssessmentQuery(get_called_class(), ['type' => FeeType::TYPE_REINST, 'tableName' => self::tableName()]);
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
        $this->fee_type = FeeType::TYPE_REINST;
        $this->assessment_amt = $model->getFee(ReinstateForm::FEE_REINST)['amt'];
        $this->purpose = 'Assessed for reinstatement';
        if (!$member->addAssessment($this)) {
            Yii::error('*** RA010 Unable to save assessment.  Error: ' . print_r($this->errors, true));
            return false;
        }
        return true;
    }
}