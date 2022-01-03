<?php

namespace app\models\member;

use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMember;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MemberSubscriptionEvents".
 *
 * @property string $event_id
 * @property string $customer_id
 * @property string $invoice_id
 * @property string $created_dt
 * @property float $charge_amt
 * @property string|null $status
 * @property int|null $receipt_id
 * @property int|null $next_attempt
 *
 * @property Member $member
 * @property ReceiptMember $receipt
 */
class SubscriptionEvent extends ActiveRecord
{
    const STATUS_PAID = 'P';
    const STATUS_FAILED = 'F';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_FAILED => 'Failed',
            self::STATUS_PAID => 'Paid',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MemberSubscriptionEvents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'customer_id', 'invoice_id', 'created_dt', 'charge_amt'], 'required'],
            [['receipt_id', 'next_attempt'], 'integer'],
            [['charge_amt'], 'number'],
            [['created_dt', 'status'], 'string'],
            [['event_id', 'customer_id', 'invoice_id'], 'string', 'max' => 50],
            [['event_id'], 'unique'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['customer_id' => 'stripe_id']],
            [['receipt_id'], 'exist', 'skipOnError' => true, 'targetClass' => Receipt::className(), 'targetAttribute' => ['receipt_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'event_id' => 'Event ID',
            'customer_id' => 'Customer ID',
            'invoice_id' => 'Ref #',
            'created_dt' => 'Created',
            'charge_amt' => 'Charge',
            'status' => 'Status',
            'receipt_id' => 'Receipt ID',
            'next_attempt' => 'Next Try',
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['stripe_id' => 'customer_id']);
    }

    /**
     * Gets query for [[ReceiptMember]].
     *
     * @return ActiveQuery
     */
    public function getReceipt()
    {
        return $this->hasOne(ReceiptMember::className(), ['id' => 'receipt_id']);
    }

    public function getStatusText($code = null)
    {
        $status = isset($code) ? $code : $this->status;
        $options = self::getStatusOptions();
        return isset($options[$status]) ? $options[$status] : "Unknown Status Type `$status`";
    }


}
