<?php

namespace app\models\member;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MemberSubscriptions".
 *
 * @property int $id
 * @property string $member_id
 * @property string $stripe_id
 *
 * @property Member $member
 */
class Subscription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MemberSubscriptions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'stripe_id'], 'required'],
            [['member_id'], 'string', 'max' => 11],
            [['stripe_id'], 'string', 'max' => 50],
            [['member_id'], 'unique'],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'stripe_id' => 'Stripe ID',
        ];
    }

    /**
     * Gets query for [[Member]].
     *
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }
}
