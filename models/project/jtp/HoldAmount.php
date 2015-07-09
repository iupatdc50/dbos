<?php

namespace app\models\project\jtp;

use Yii;
use app\models\project\jtp\Project;

/**
 * This is the model class for table "HoldAmounts".
 *
 * @property string $project_id
 * @property number $hold_amt
 * 
 * @property Project $project
 */
class HoldAmount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'HoldAmounts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id'], 'required'],
            [['hold_amt'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Project ID',
            'hold_amt' => 'Hold Amount',
        ];
    }
    
    public function getProject()
    {
    	return $this->hasOne(Project::className(),  ['project_id' => 'project_id']);
    }
}
