<?php

namespace app\models\training;

use app\models\value\Lob;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "WorkHours".
 *
 * @property integer $id
 * @property integer $timesheet_id
 * @property string $lob_cd
 * @property integer $wp_seq
 * @property string $hours
 *
 * @property Timesheet $timesheet
 * @property Lob $lob
 * @property WorkProcess $workProcess
 */
class WorkHour extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WorkHours';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['timesheet_id', 'lob_cd', 'wp_seq'], 'required'],
            [['timesheet_id', 'wp_seq'], 'integer'],
            [['hours'], 'number'],
            [['lob_cd'], 'string', 'max' => 4],
            [['timesheet_id'], 'exist', 'skipOnError' => true, 'targetClass' => Timesheet::className(), 'targetAttribute' => ['timesheet_id' => 'id']],
            [['lob_cd'], 'exist', 'skipOnError' => true, 'targetClass' => Lob::className(), 'targetAttribute' => ['lob_cd' => 'lob_cd']],
            [['lob_cd', 'wp_seq'], 'exist', 'skipOnError' => true, 'targetClass' => WorkProcess::className(), 'targetAttribute' => ['lob_cd' => 'lob_cd', 'wp_seq' => 'seq']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'timesheet_id' => 'Timesheet ID',
            'lob_cd' => 'Lob Cd',
            'wp_seq' => 'Wp Seq',
            'hours' => 'Hours',
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->timesheet->popTotalHours();

    }

    /**
     * @return ActiveQuery
     */
    public function getTimesheet()
    {
        return $this->hasOne(Timesheet::className(), ['id' => 'timesheet_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    /**
     * @return ActiveQuery
     */
    public function getWorkProcess()
    {
        return $this->hasOne(WorkProcess::className(), ['lob_cd' => 'lob_cd', 'seq' => 'wp_seq']);
    }
}
