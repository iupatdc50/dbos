<?php

namespace app\models\member;

use app\components\behaviors\OpImageBehavior;
use BadMethodCallException;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use app\models\value\Lob;
use app\models\base\BaseEndable;
use app\models\accounting\BaseAllocation;
use app\components\validators\AtLeastValidator;
use yii\web\UploadedFile;

/**
 * This is the model class for table "MemberStatuses".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $lob_cd
 * @property string $member_status
 * @property string $reason
 * @property integer $alloc_id
 * @property string $doc_id
 *
 * @property Member $member
 * @property Lob $lob
 * @property array $lobOptions
 * @property StatusCode $status
 * @property array $statusOptions
 * @property BaseAllocation $allocation
 *
 * @method UploadedFile uploadImage()
 * @method getImagePath()
 * @method deleteImage()
 */
class Status extends BaseEndable
{
	CONST SCENARIO_CCD = 'ccd';
	CONST SCENARIO_DEPINSVC = 'dep-insvc';
	CONST SCENARIO_RESET = 'reset';
	
	CONST REASON_NEW = 'New Entry';
	CONST REASON_APF = 'APF satisfied';
	CONST REASON_DUES = 'Dues payment made';
	CONST REASON_CCG = 'CC granted to local: ';
	CONST REASON_CCD = 'Deposit CC. Previous local: ';
	CONST REASON_DROP = 'Member dropped';
	CONST REASON_SUSP = 'Member suspended';
	CONST REASON_FORFEIT = 'Forfeited';
	CONST REASON_DEPINSVC = 'Deposit ISC';
	CONST REASON_REINST = 'Member reinstated';
	CONST REASON_RESET_INIT = 'Initiation Date reset to: ';
	CONST REASON_RESET_PT = 'Dues Thru Date reset to: ';
	
	CONST ACTIVE = 'A';
	CONST INACTIVE = 'I';
	CONST IN_APPL = 'N';
	CONST SUSPENDED = 'S';
	CONST GRANTINSVC = 'M';
	CONST STUB = 'U';
	CONST OUTOFSTATE = 'O';
	
	public $other_local;
	public $paid_thru_dt;
	public $init_dt;

    /**
     * @var mixed	Stages document to be uploaded
     */
    public $doc_file;

    // Holds the temporary state between events
    private $_image;
    private $_hold_path;

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
     * Handles all the document attachment processing functions for the model
     *
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            OpImageBehavior::className(),
        ];
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
            [['doc_id'], 'string', 'max' => 20],
            [['doc_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'pdf, png'],

        	[['other_local'], 'required', 'on' => self::SCENARIO_CCD],
        	['init_dt', AtLeastValidator::className(), 'in' => ['paid_thru_dt', 'init_dt'], 'on' => self::SCENARIO_RESET],
        	['alloc_id', 'exist', 'targetClass' => '\app\models\accounting\BaseAllocation', 'targetAttribute' => 'id'],
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

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $hold_id = null;
            if (!$insert) {
                $this->_hold_path = $this->getImagePath();
                $hold_id = $this->doc_id;
            }

            $this->_image = $this->uploadImage();

            if ($this->_image === false)
                $this->doc_id = $hold_id;

            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->_image !== false && (($this->_hold_path === null) || unlink($this->_hold_path)))
            $this->_image->saveAs($this->getImagePath());
        if (!$insert && isset($changedAttributes['effective_dt']) && ($this->member_status = Status::IN_APPL)) {
            $this->member->application_dt = $this->effective_dt;
            $this->member->save();
        }
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteImage();
            return true;
        }
        return false;
    }

    public function getIsActive()
    {
    	return $this->member_status == self::ACTIVE;
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
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }
    
    public function getLobOptions()
    {
    	return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(StatusCode::className(), ['member_status_cd' => 'member_status']);
    }

    public function getAllocation()
    {
        return $this->hasOne(BaseAllocation::className(), ['id' => 'alloc_id']);
    }
    
    public function getStatusOptions()
    {
    	return ArrayHelper::map(StatusCode::find()->orderBy('member_status_cd')->all(), 'member_status_cd', 'descrip');
    }

    /**
     * Generate member status for reinstate.  If successful, APF dates on member record are
     * moved forward. Assumes that effective_dt is set.
     *
     * @param Member $member
     * @param bool $reset_init  Makes init_dt, application_dt current
     * @return bool
     */
    public function makeReinstate (Member $member, $reset_init = true)
    {
        if (!isset($this->effective_dt)) {
            Yii::error('*** MST010 Missing effective date');
            throw new BadMethodCallException('Missing effective_dt');
        }
        $this->member_status = self::ACTIVE;
        $this->reason = self::REASON_REINST;
        if ($member->addStatus($this)) {
            if ($reset_init) {
                $member->init_dt = $this->effective_dt;
                $member->application_dt = $this->effective_dt;
                $member->save();
            }
        }
        return true;

    }

}
