<?php

namespace app\models\accounting;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "DuesStripeProducts".
 *
 * @property int $dues_rate_id
 * @property string $stripe_id Stripe Product `id` column
 * @property string $stripe_price_id
 *
 * @property DuesRate $duesRate
 */
class DuesStripeProduct extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'DuesStripeProducts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dues_rate_id', 'stripe_id', 'stripe_price_id'], 'required'],
            [['dues_rate_id'], 'integer'],
            [['stripe_id', 'stripe_price_id'], 'string', 'max' => 50],
            [['dues_rate_id'], 'unique'],
            [['dues_rate_id'], 'exist', 'skipOnError' => true, 'targetClass' => DuesRate::className(), 'targetAttribute' => ['dues_rate_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'dues_rate_id' => 'Dues Rate ID',
            'stripe_id' => 'Stripe ID',
            'stripe_price_id' => 'Stripe Price ID',
        ];
    }

    /**
     * Gets query for [[DuesRate]].
     *
     * @return ActiveQuery
     */
    public function getDuesRate()
    {
        return $this->hasOne(DuesRate::className(), ['id' => 'dues_rate_id']);
    }
}
