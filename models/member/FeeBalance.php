<?php

namespace app\models\member;

use app\modules\admin\models\FeeType;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "FeeBalances".
 *
 * @property string $member_id
 * @property string $fee_type
 * @property float|null $balance_amt
 *
 * @property FeeType $feeType
 */
class FeeBalance extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'FeeBalances';
    }

    public static function primaryKey()
    {
        return ['member_id', 'fee_type'];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'fee_type' => 'Fee Type',
            'balance_amt' => 'Alloc Balance',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
    }
}
