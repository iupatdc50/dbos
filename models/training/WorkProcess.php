<?php

namespace app\models\training;

use app\models\value\Lob;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "WorkProcesses".
 *
 * @property integer $id
 * @property string $lob_cd
 * @property integer $seq
 * @property string $work_process
 * @property integer $hours
 * @property string $short_descrip [varchar(30)]
 *
 * @property WorkHour[] $workHours
 * @property Lob $lob
 * @property string $descrip
 */
class WorkProcess extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'WorkProcesses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'seq', 'work_process', 'short_descrip'], 'required'],
            [['seq', 'hours'], 'integer'],
            [['lob_cd'], 'string', 'max' => 4],
            [['work_process'], 'string', 'max' => 50],
            [['short_descrip'], 'string', 'max' => 30],
            [['lob_cd', 'seq'], 'unique', 'targetAttribute' => ['lob_cd', 'seq'], 'message' => 'The combination of Lob Cd and Seq has already been taken.'],
            [['lob_cd'], 'exist', 'skipOnError' => true, 'targetClass' => Lob::className(), 'targetAttribute' => ['lob_cd' => 'lob_cd']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lob_cd' => 'Lob Cd',
            'seq' => 'Seq',
            'work_process' => 'Work Process',
            'hours' => 'Hours',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getWorkHours()
    {
        return $this->hasMany(WorkHour::className(), ['lob_cd' => 'lob_cd', 'wp_seq' => 'seq']);
    }

    /**
     * @return ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    /**
     * @return string
     */
    public function getDescrip()
    {
        return $this->seq . ': ' . $this->short_descrip;
    }
}
