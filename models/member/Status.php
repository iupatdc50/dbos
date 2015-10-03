<?php

namespace app\models\member;

use Yii;
use yii\db\Query;
use app\models\value\Lob;
use app\models\member\Member;
use app\models\member\StatusCode;

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
class Status extends \yii\db\ActiveRecord
{
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
            [['effective_dt', 'end_dt'], 'date'],
            [['reason'], 'string'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
        	[['member_status'], 'exist', 'targetClass' => '\app\models\member\StatusCode'],
        	[['lob_cd'], 'exist', 'targetClass' => '\app\models\value\Lob'],
        	[['zip_cd'], 'exist', 'targetClass' => '\app\models\ZipCode'],
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
    
    /**
     * On a new member entry, the status defaults to active and the effective date is the
     * application date
     * 
     * @param Member $member
     * @param string $reason
     * @throws \yii\base\InvalidConfigException
     */
    public function configureNewEntry(Member $member, $reason = 'New entry')
    {
    	if (!(isset($member) && ($member instanceof Member)))
    		throw new \yii\base\InvalidConfigException('No member object injected');
    	if (!isset($this->lob_cd))
    		throw new \yii\base\InvalidConfigException('LOB code must be provided on a new entry');
    	$this->member_id = $member->member_id;
    	$this->member_status = 'A';
    	$this->effective_dt = $member->application_dt;
    	$this->reason =  $reason;
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(StatusCode::className(), ['member_status_cd' => 'member_status']);
    }
        
}
