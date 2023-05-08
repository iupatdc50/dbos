<?php

namespace app\models\member;

use Throwable;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * This is the model class for table "MemberSubscriptions".
 *
 * @property int $id
 * @property string $member_id
 * @property string $stripe_id
 * @property string $is_active
 *
 * @property Member $member
 * @property SubscriptionEvent[] $events
 */
class Subscription extends ActiveRecord
{
    const STATUS_CANCELED = 'canceled';
    const STATUS_PASTDUE = 'past_due';

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
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete())
            return false;

        foreach ($this->events as $event)
            $event->delete();

        return true;
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

    /**
     * @return ActiveQuery
     */
    public function getEvents()
    {
        return $this->hasMany(SubscriptionEvent::className(), ['customer_id' => 'stripe_id'])
            ->via('member');
    }

}
