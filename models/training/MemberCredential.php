<?php

namespace app\models\training;

use Yii;
use app\models\member\Member;

/**
 * This is the model class for table "MemberCredentials".
 *
 * @property integer $id
 * @property string $member_id
 * @property integer $credential_id
 * @property string $complete_dt
 * @property string $expire_dt
 * @property string $catg
 * @property integer display_seq
 *
 * @property Member $member
 * @property CredCategory $category
 */
class MemberCredential extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberCredentials';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'credential_id', 'complete_dt'], 'required'],
            [['credential_id'], 'integer'],
            [['complete_dt', 'expire_dt'], 'safe'],
            [['member_id'], 'string', 'max' => 11],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
            [['credential_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credential::className(), 'targetAttribute' => ['credential_id' => 'id']],
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
            'complete_dt' => 'Complete Dt',
            'expire_dt' => 'Expire Dt',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    public function getCategory()
    {
        return $this->hasOne(CredCategory::className(), ['catg' => 'catg']);
    }
}
