<?php

namespace app\models\training;

use Yii;

/**
 * This is the model class for table "WorkHoursSumm".
 *
 * @property string $member_id
 * @property integer $wp_seq
 * @property string $work_process
 * @property string $SUM(hours)
 */
class WorkHoursSummary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WorkHoursSumm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'wp_seq', 'work_process'], 'required'],
            [['wp_seq'], 'integer'],
            [['SUM(hours)'], 'number'],
            [['member_id'], 'string', 'max' => 11],
            [['work_process'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'wp_seq' => 'Wp Seq',
            'work_process' => 'Work Process',
            'SUM(hours)' => 'Sum(hours)',
        ];
    }
}
