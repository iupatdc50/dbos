<?php

namespace app\models\member;

use Yii;

/**
 * This is the model class for table "MemberEmails".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $email
 *
 * @property Members $member
 */
class Email extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberEmails';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'email'], 'required'],
            [['member_id'], 'string', 'max' => 11],
            [['email'], 'email'],
            [['email'], 'unique'],
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
            'email' => 'Email',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }
}
