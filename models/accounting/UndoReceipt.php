<?php

namespace app\models\accounting;

/**
 * This is the model class for table "UndoReceipts".
 *
 * @property integer $id
 * @property string $payor_nm
 * @property string $payment_method
 * @property string $payor_type
 * @property string $received_dt
 * @property string $received_amt
 * @property string $unallocated_amt
 * @property integer $created_at
 * @property integer $created_by
 * @property string $remarks
 * @property string $tracking_nbr
 * @property string $helper_dues
 * @property string $helper_hrs
 * @property string $lob_cd
 * @property string $acct_month
 * @property string $void
 */
class UndoReceipt extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'UndoReceipts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by'], 'integer'],
            [['payment_method', 'payor_type', 'remarks', 'void'], 'string'],
            [['received_dt'], 'safe'],
            [['received_amt', 'unallocated_amt', 'helper_dues', 'helper_hrs'], 'number'],
            [['payor_nm'], 'string', 'max' => 100],
            [['tracking_nbr'], 'string', 'max' => 20],
            [['lob_cd'], 'string', 'max' => 4],
            [['acct_month'], 'string', 'max' => 6],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payor_nm' => 'Payor Nm',
            'payment_method' => 'Payment Method',
            'payor_type' => 'Payor Type',
            'received_dt' => 'Received Dt',
            'received_amt' => 'Received Amt',
            'unallocated_amt' => 'Unallocated Amt',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'remarks' => 'Remarks',
            'tracking_nbr' => 'Tracking Nbr',
            'helper_dues' => 'Helper Dues',
            'helper_hrs' => 'Helper Hrs',
            'lob_cd' => 'Lob Cd',
            'acct_month' => 'Acct Month',
            'void' => 'Void',
        ];
    }
}
