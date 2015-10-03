<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "ArchiveBalances".
 *
 * @property string $member_id
 * @property string $fee_type
 * @property string $cutoff_dt
 * @property string $balance_amt
 */
class ArchiveBalance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ArchiveBalances';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'fee_type', 'cutoff_date'], 'required'],
            [['cutoff_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['balance_amt'], 'number'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
            [['fee_type'], 'exist', 'targetClass' => '\app\models\value\FeeType']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'fee_type' => 'Fee Type',
            'cutoff_dt' => 'Cutoff Date',
            'balance_amt' => 'Balance',
        ];
    }
}
