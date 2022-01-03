<?php

namespace app\models\accounting;

use app\models\member\Member;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "Transactions".
 *
 * @property string $transaction_id
 * @property string|null $customer_id
 * @property string|null $tracking_nbr
 * @property int|null $receipt_id
 * @property int|null $created_at
 */
class StripeTransaction extends ActiveRecord
{
    // Auto recurring charge from subscription
    const TYPE_AUTO = 'A';
    // Manual one-time charge
    const TYPE_MANUAL = 'M';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'StripeTransactions';
    }

    /**
     * Grabs a unique code (UUID) to tracking stripe transactions
     *
     * @return false|string|null
     * @throws Exception
     */
    public static function getTracking()
    {
        $db = Yii::$app->db;
        return $db->createCommand('SELECT UUID_SHORT();')->queryScalar();
    }

    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::className(), 'updatedAtAttribute' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaction_id'], 'required'],
            [['receipt_id', 'created_at'], 'integer'],
            [['transaction_id', 'customer_id'], 'string', 'max' => 100],
            [['tracking_nbr'], 'string', 'max' => 20],
            [['tracking_nbr'], 'unique'],
            [['transaction_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'transaction_id' => 'Transaction ID',
            'customer_id' => 'Customer ID',
            'tracking_nbr' => 'Tracking Nbr',
            'receipt_id' => 'Receipt ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['stripe_id' => $this->customer_id]);
    }


}
