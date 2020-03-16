<?php

namespace app\models\member;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "AllBalances".
 *
 * @property string $member_id
 * @property float|null $total_due
 */
class AllBalance extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'AllBalances';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'total_due' => 'Total Due',
        ];
    }
}
