<?php

namespace app\models\training;

use Yii;
use app\models\member\Member;

/**
 * This is the model class for table "CurrentMemberCredentials".
 *
 * @property integer $id
 * @property string $member_id
 * @property integer $credential_id
 * @property string $complete_dt
 * @property string $expire_dt
 * @property string $catg
 *
 * @property Member $member
 * @property Credential $credential
 */
class CurrentMemberCredential extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CurrentMemberCredentials';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'credential_id'], 'integer'],
            [['member_id', 'credential_id', 'complete_dt', 'catg'], 'required'],
            [['complete_dt', 'expire_dt'], 'safe'],
            [['member_id'], 'string', 'max' => 11],
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
            'credential_id' => 'Credential ID',
            'complete_dt' => 'Completed',
            'expire_dt' => 'Expires',
        ];
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
    public function getCredential()
    {
        return $this->hasOne(Credential::className(), ['id' => 'credential_id']);
    }
}
