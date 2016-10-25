<?php

namespace app\models\member;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\value\Lob;
use app\models\member\Member;
use app\models\member\StatusCode;
use app\models\base\BaseEndable;

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
	
	CONST REASON_NEW = 'New Entry';
	CONST REASON_APF = 'APF satisfied';
	CONST REASON_DUES = 'Dues payment made';
	
	CONST ACTIVE = 'A';
	CONST INACTIVE = 'I';
	CONST IN_APPL = 'N';
	CONST SUSPENDED = 'S';
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberStatuses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'effective_dt', 'lob_cd'], 'required'],
            [['effective_dt', 'end_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['reason'], 'string'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
        	[['member_status'], 'exist', 'targetClass' => '\app\models\member\StatusCode', 'targetAttribute' => 'member_status_cd'],
        	[['lob_cd'], 'exist', 'targetClass' => '\app\models\value\Lob'],
            [['member_id', 'effective_dt'], 'unique', 'targetAttribute' => ['member_id', 'effective_dt'], 'message' => 'The combination of Member ID and Effective Dt has already been taken.']
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
        ];
    }
    
    public function getIsActive()
    {
    	return $this->member_status == 'A';
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
    
}
