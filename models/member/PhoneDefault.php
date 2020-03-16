<?php

namespace app\models\member;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MemberPhonesDefault".
 *
 * @property string $member_id
 * @property integer $phone_id
 *
 * @property Member $member
 * @property Phone $phone
 */
class PhoneDefault extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberPhonesDefault';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'phone_id'], 'required'],
            [['phone_id'], 'integer'],
            [['member_id'], 'string', 'max' => 11],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
            [['phone_id'], 'exist', 'skipOnError' => true, 'targetClass' => Phone::className(), 'targetAttribute' => ['phone_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'phone_id' => 'Phone ID',
        ];
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
    public function getPhone()
    {
        return $this->hasOne(Phone::className(), ['id' => 'phone_id']);
    }
}
