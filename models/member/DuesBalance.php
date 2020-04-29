<?php

namespace app\models\member;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "DuesBalances".
 *
 * @property string $member_id
 * @property float|null $balance_amt
 *
 * @property Member $member
 */
class DuesBalance extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'DuesBalances';
    }

    public static function primaryKey()
    {
        return ['member_id'];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'balance_amt' => 'Dues Balance',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

}
