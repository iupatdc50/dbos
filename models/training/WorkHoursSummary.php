<?php

namespace app\models\training;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "WorkHoursSumm".
 *
 * @property string $member_id
 * @property integer $wp_seq
 * @property string $work_process
 * @property string $hours [decimal(31,2)]
 * @property int $target [int(11)]
 */
class WorkHoursSummary extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WorkHoursSumm';
    }

    /**
     * In the VIEW, work_process returns the seq column concatenated with the short_descrip column
     *
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'wp_seq', 'work_process'], 'required'],
            [['wp_seq'], 'integer'],
            [['hours'], 'number'],
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
            'hours' => 'Hours',
        ];
    }
}
