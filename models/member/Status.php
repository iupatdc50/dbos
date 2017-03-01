<?php

namespace app\models\member;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\value\Lob;
use app\models\member\Member;
use app\models\member\StatusCode;
use app\models\base\BaseEndable;
use app\components\validators\AtLeastValidator;

/**
 * This is the model class for table "MemberStatuses".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $lob_cd
 * @property string $member_status
 * @property string $reason
 *
 * @property Lob $lob
 * @property StatusCode $status
 */
class Status extends BaseEndable
{
	CONST SCENARIO_CCD = 'ccd';
	CONST SCENARIO_RESET = 'reset';
	
	CONST REASON_NEW = 'New Entry';
	CONST REASON_APF = 'APF satisfied';
	CONST REASON_DUES = 'Dues payment made';
	CONST REASON_CCG = 'CC granted to local: ';
	CONST REASON_CCD = 'CC deposited. Previous local: ';
	CONST REASON_DROP = 'Member dropped';
	CONST REASON_FORFEIT = 'Forfeited';
	CONST REASON_REINST = 'Member reinstated';
	CONST REASON_RESET_INIT = 'Initiation Date reset to: ';
	CONST REASON_RESET_PT = 'Dues Thru Date reset to: ';
	
	CONST ACTIVE = 'A';
	CONST INACTIVE = 'I';
	CONST IN_APPL = 'N';
	CONST SUSPENDED = 'S';
	
	public $other_local;
	public $paid_thru_dt;
	public $init_dt;
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberStatuses';
    }
    
    public static function qualifier()
    {
    	return 'member_id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'effective_dt', 'lob_cd', 'member_status'], 'required'],
            [['effective_dt', 'end_dt', 'paid_thru_dt', 'init_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['reason'], 'string'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
        	[['member_status'], 'exist', 'targetClass' => '\app\models\member\StatusCode', 'targetAttribute' => 'member_status_cd'],
        	[['lob_cd'], 'exist', 'targetClass' => '\app\models\value\Lob'],
            ['effective_dt', 'unique', 'targetAttribute' => ['member_id', 'effective_dt'], 'message' => 'The Effective Date has already been taken.'],
        		
        	[['other_local'], 'required', 'on' => self::SCENARIO_CCD],
        	['init_dt', AtLeastValidator::className(), 'in' => ['paid_thru_dt', 'init_dt'], 'on' => self::SCENARIO_RESET],
        		
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'lob_cd' => 'Local',
            'member_status' => 'Status',
            'reason' => 'Reason',
        	'other_local' => 'Previous Local',
        	'paid_thru_dt' => 'New Paid Thru',
        	'init_dt' => 'New Initiation',
        ];
    }
        
    public function getIsActive()
    {
    	return $this->member_status == self::ACTIVE;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }
    
    public function getLobOptions()
    {
    	return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(StatusCode::className(), ['member_status_cd' => 'member_status']);
    }
    
    public function getStatusOptions()
    {
    	return ArrayHelper::map(StatusCode::find()->orderBy('member_status_cd')->all(), 'member_status_cd', 'descrip');
    }
    
}
