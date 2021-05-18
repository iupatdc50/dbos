<?php

namespace app\models\accounting;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "BillPayments".
 *
 * @property int $id
 * @property int $bill_id
 * @property int $receipt_id
 * @property string|null $transmittal
 *
 * @property GeneratedBill $bill
 * @property Receipt $receipt
 */
class BillPayment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BillPayments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bill_id', 'receipt_id'], 'required'],
            [['bill_id', 'receipt_id'], 'integer'],
            [['transmittal'], 'string', 'max' => 25],
            [['bill_id', 'receipt_id'], 'unique', 'targetAttribute' => ['bill_id', 'receipt_id']],
            [['bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => GeneratedBill::className(), 'targetAttribute' => ['bill_id' => 'id']],
            [['receipt_id'], 'exist', 'skipOnError' => true, 'targetClass' => Receipt::className(), 'targetAttribute' => ['receipt_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bill_id' => 'Bill ID',
            'receipt_id' => 'Receipt ID',
            'transmittal' => 'Transmittal',
        ];
    }

    /**
     * Gets query for [[Bill]].
     *
     * @return ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(GeneratedBill::className(), ['id' => 'bill_id']);
    }

    /**
     * Gets query for [[Receipt]].
     *
     * @return ActiveQuery
     */
    public function getReceipt()
    {
        return $this->hasOne(Receipt::className(), ['id' => 'receipt_id']);
    }

}
