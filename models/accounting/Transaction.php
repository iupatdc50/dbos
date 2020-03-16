<?php

namespace app\models\accounting;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "Transactions".
 *
 * @property string $transaction_id
 * @property string|null $customer_id
 * @property string|null $tracking_nbr
 * @property int|null $receipt_id
 * @property string|null $member_id
 * @property string|null $currency
 * @property float|null $charge
 * @property int|null $created_at
 * @property string|null $stripe_status
 * @property string|null $dbos_status
 */
class Transaction extends ActiveRecord
{
    const STRIPE_SUCCEEDED = 'succeeded';
    const DBOS_INPROGRESS = 'in progress';
    const DBOS_COMPLETED = 'completed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Transactions';
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaction_id'], 'required'],
            [['receipt_id', 'created_at'], 'integer'],
            [['charge'], 'number'],
            [['transaction_id', 'customer_id'], 'string', 'max' => 100],
            [['tracking_nbr', 'stripe_status', 'dbos_status'], 'string', 'max' => 20],
            [['member_id'], 'string', 'max' => 11],
            [['currency'], 'string', 'max' => 3],
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
            'member_id' => 'Member ID',
            'currency' => 'Currency',
            'charge' => 'Charge',
            'created_at' => 'Created At',
            'stripe_status' => 'Stripe Status',
            'dbos_status' => 'Dbos Status',
        ];
    }
}
