<?php

namespace app\models\member;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "QualifiesForIncrease".
 *
 * @property string $member_id
 * @property float|null $total_hours
 * @property int $wage_percent
 * @property int|null $should_be
 */
class QualifiesForIncrease extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'QualifiesForIncrease';
    }

    public static function primaryKey()
    {
        return ['member_id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['total_hours'], 'number'],
            [['wage_percent', 'should_be'], 'integer'],
            [['member_id'], 'string', 'max' => 11],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'total_hours' => 'Total Hours',
            'wage_percent' => 'Wage Percent',
            'should_be' => 'Should Be',
        ];
    }
}

