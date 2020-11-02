<?php


namespace app\models\member;

use app\components\utilities\OpDate;
use app\helpers\OptionHelper;
use app\models\accounting\AdminFee;
use app\models\accounting\DuesRateFinder;
use app\models\accounting\FeeCalendar;
use app\models\accounting\InitFee;
use app\models\user\User;
use app\modules\admin\models\FeeType;
use yii\base\Model;

class ReinstateForm extends Model
{
    CONST TYPE_APF = 'A';
    CONST TYPE_BACKDUES = 'B';
    CONST TYPE_WAIVE = 'W';

    CONST FEE_DUES = 'D';
    CONST FEE_APF = 'A';
    CONST FEE_DUESINIT = 'I';
    CONST FEE_REINST = 'R';
    CONST FEE_REINSTINIT = 'T';

    // Maximum months for reinstatement dues included in APF
    CONST INIT_MAX = 6;

    private $_fees = [];
    private $_options = [];
    private $_authUser;

    /* @var Member $member */
    public $member;

    public $type;
    // for APF
    public $assessments_a = [];
    // for back dues
    public $assessments_b = [];
    // user ID
    public $authority;

    public static function getAllowedTypes()
    {
        return [
            self::TYPE_APF,
            self::TYPE_BACKDUES,
            self::TYPE_WAIVE,
        ];
    }

    public function rules()
    {
        return [
            ['type', 'required'],
            ['type', 'in', 'range' => self::getAllowedTypes()],
            ['assessments_a', 'required', 'when' => function($model) {
                return ($model->type == self::TYPE_APF);
            }, 'whenClient' => "function (attribute, value) {
            	return ($('#type').val() == 'A');
    		}", 'message' => 'Please select at least one fee'],
            ['assessments_b', 'safe'],
            ['authority', 'required', 'when' => function($model) {
                return (
                    $model->type == self::TYPE_WAIVE
                    || (($model->type == self::TYPE_APF) && (count($model->assessments_a) < count($this->getAssessmentOptions(self::TYPE_APF))))
                );
            }, 'whenClient' => "function (attribute, value) {
            	return ($('#type').val() == 'W');
    		}", 'message' => 'Waiver authority is required'],
        ];

    }

    public function attributeLabels()
    {
        return [
            'type' => 'Reinstatement Method',
            'assessments_a' => 'Check all that apply',
            'assessments_b' => 'Status will change to suspended with following due',
            'authority' => 'Authorized by',
        ];
    }

    /**
     * @todo Use DuesRateFinder after mods to compute with FeeCalendar
     * @see DuesRateFinder
     */
    public function init()
    {
        $member = $this->member;
        $lob_cd = $member->currentStatus->lob_cd;
        $member_class = $member->currentClass->member_class;
        $rate_class = $member->currentClass->rate_class;

        $this->_fees[self::FEE_DUES] = [
            'amt' => FeeCalendar::getTrueDuesBalance($lob_cd, $rate_class, $member->dues_paid_thru_dt),
            'descrip' => 'dues: ',
        ];

        // Replace this code with DuesRateFinder (see todo)
        $init = InitFee::findOne(['lob_cd' => $lob_cd, 'member_class' => $member_class, 'end_dt' => null]);
        $amt = $init->fee;
        if ($init->included == OptionHelper::TF_TRUE)
            $amt -= FeeCalendar::getTrueDuesBalance($lob_cd, $rate_class, $member->dues_paid_thru_dt, $init->dues_months);
        $this->_fees[self::FEE_APF] = [
            'amt' => $amt,
            'descrip' => 'init fee: ',
        ];

        $this->_fees[self::FEE_DUESINIT] = [
            'amt' => FeeCalendar::getTrueDuesBalance($lob_cd, $rate_class, $member->dues_paid_thru_dt, self::INIT_MAX),
            'descrip' => 'dues: ',
        ];
        $reinst_amt = AdminFee::getFee(FeeType::TYPE_REINST, $this->getToday()->getMysqlDate());
        $this->_fees[self::FEE_REINST] = [
            'amt' => $reinst_amt,
            'descrip' => 'reinst: ',
        ];
        $this->_fees[self::FEE_REINSTINIT] = [
            'amt' => $reinst_amt,
            'descrip' => 'reinst: ',
        ];

        $this->_options = [
            self::TYPE_BACKDUES => [
                self::FEE_DUES => 'Monthly dues balance $' . number_format($this->_fees[self::FEE_DUES]['amt'], 2),
                self::FEE_REINST => 'Reinstatement fee $' . number_format($this->_fees[self::FEE_REINST]['amt'], 2),
            ],
            self::TYPE_APF => [
                self::FEE_APF => 'Assess initiation fee (APF) of $'. number_format($this->_fees[self::FEE_APF]['amt'], 2),
                self::FEE_REINSTINIT => 'Assess reinstatement of $'. number_format($this->_fees[self::FEE_REINSTINIT]['amt'], 2) . ' in APF balance',
                self::FEE_DUESINIT => 'Assess dues of $'. number_format($this->_fees[self::FEE_DUESINIT]['amt'], 2) . ' in APF balance',
            ],
        ];

    }

    public function getTypeOptions()
    {
        return [
            self::TYPE_APF => 'Initiation (APF)',
            self::TYPE_BACKDUES => 'Back dues owed',
            self::TYPE_WAIVE => 'Waive all fees',
        ];
    }

    /**
     * @param $type string  Should be one of the TYPE_ constants
     * @return array
     */
    public function getAssessmentOptions($type)
    {
        return $this->_options[$type];
    }

    /**
     * @param $type string Should be one of the FEE_ constants
     * @return array
     */
    public function getFee($type)
    {
        return $this->_fees[$type];
    }

    public function getAuthUser()
    {
        if(!$this->_authUser)
            $this->_authUser = User::findOne($this->authority);
        return $this->_authUser;
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

}