<?php

namespace app\models\accounting;

/**
 * This is the model class for table "DuesAllocPickList".
 *
 * @property integer $receipt_id
 * @property string $member_id
 * @property string $received_dt
 * @property string $allocation_amt
 * @property string $paid_thru_dt
 * @property integer $id
 * @property string $descrip
 * @property string $overage [decimal(9,2)]
 */
class DuesAllocPickList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DuesAllocPickList';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id', 'member_id', 'received_dt', 'allocation_amt'], 'required'],
            [['receipt_id', 'id'], 'integer'],
            [['received_dt', 'paid_thru_dt', 'overage'], 'safe'],
            [['allocation_amt'], 'number'],
            [['member_id'], 'string', 'max' => 11],
            [['descrip'], 'string', 'max' => 35],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'receipt_id' => 'Receipt ID',
            'member_id' => 'Member ID',
            'received_dt' => 'Received Dt',
            'allocation_amt' => 'Allocation Amt',
            'paid_thru_dt' => 'Paid Thru Dt',
            'id' => 'ID',
            'descrip' => 'Descrip',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAllocation()
    {
        return $this->hasOne(DuesAllocation::className(), ['id' => 'id']);
    }

}


