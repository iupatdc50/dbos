<?php

namespace app\models\training;

use app\models\member\Member;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MemberScheduled".
 *
 * @property integer $id
 * @property string $member_id
 * @property integer $credential_id
 * @property string $schedule_dt
 *
 * @property Credential $credential
 */
class MemberScheduled extends ActiveRecord
{
    /*
     * Injected Member object, used for creating new entries
     */
    public $member;

    public $catg;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberScheduled';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['credential_id', 'schedule_dt'], 'required'],
            [['credential_id'], 'integer'],
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
            'credential_id' => 'Credential',
            'schedule_dt' => 'Scheduled',
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!(isset($this->member) && ($this->member instanceof Member)))
                throw new InvalidConfigException('No member object injected');
            if ($insert)
                $this->member_id = $this->member->member_id;
            return true;
        }
        return false;

    }
    /**
     * @return ActiveQuery
     */
    public function getCredential()
    {
        return $this->hasOne(Credential::className(), ['id' => 'credential_id']);
    }

    /**
     * @param $catg
     * @return array
     */
    public function getCredentialOptions($catg)
    {
        return ArrayHelper::map(Credential::find()->where(['catg' => $catg])->orderBy('display_seq')->all(), 'id', 'credential');
    }

}
