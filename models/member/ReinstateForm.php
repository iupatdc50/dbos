<?php


namespace app\models\member;

use app\models\accounting\FeeCalendar;
use app\models\accounting\InitFee;
use app\models\user\User;
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

    /* @var Member $_member */
    private $_member;
    /* @var InitFee $_init */
    private $_init;
    private $_reinst_amt;

    public $type;
    // for APF
    public $assessments_a = [];
    // for back dues
    public $assessments_b = [];
    // user ID
    public $authority;

    // Member's current status and class values.  Can be injected for testing
    public $lob_cd;
    public $rate_class;

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
     * ReinstateForm constructor.  Force injected classes
     * @param Member $member
     * @param InitFee $init
     * @param number $reinst_amt
     * @param array $config
     */
    public function __construct(Member $member, InitFee $init, $reinst_amt, $config = [])
    {
        $this->_member = $member;
        $this->_init = $init;
        $this->_reinst_amt = $reinst_amt;
        parent::__construct($config);
    }

    public function init()
    {
        $this->bootstrap();
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

    protected function bootstrap()
    {
        $lob_cd = $this->getLobCd();
        $rate_code = $this->getRateClass();
        $paid_thru_dt = $this->_member->dues_paid_thru_dt;
        $today = $this->_member->getToday();

        $this->_fees[self::FEE_DUES] = [
            'amt' => FeeCalendar::getTrueDuesBalance($lob_cd, $rate_code, $paid_thru_dt, null, $today),
            'descrip' => 'dues: ',
        ];

        $this->_fees[self::FEE_APF] = [
            'amt' => $this->_init->fee,
            'descrip' => 'init fee: ',
        ];

        $this->_fees[self::FEE_DUESINIT] = [
            'amt' => FeeCalendar::getTrueDuesBalance($lob_cd, $rate_code, $paid_thru_dt, self::INIT_MAX),
            'descrip' => 'dues: ',
        ];
        $this->_fees[self::FEE_REINST] = [
            'amt' => $this->_reinst_amt,
            'descrip' => 'reinst: ',
        ];
        $this->_fees[self::FEE_REINSTINIT] = [
            'amt' => $this->_reinst_amt,
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

    private function getLobCd()
    {
        if(!(isset($this->lob_cd)))
            $this->lob_cd = isset($this->_member->currentStatus) ? $this->_member->currentStatus->lob_cd : null;
        return $this->lob_cd;
    }

    private function getRateClass()
    {
        if(!(isset($this->rate_class)))
            $this->rate_class = isset($this->_member->currentClass) ? $this->_member->currentClass->rate_class : 'R';
        return $this->rate_class;
    }

}