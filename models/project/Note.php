<?php

namespace app\models\project;

use Yii;
use app\models\base\BaseNote;

/**
 * This is the model class for table "ProjectNotes".
 *
 * @property integer $id
 * @property string $project_id
 * @property string $note
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Projects $project
 */
class Note extends BaseNote
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ProjectNotes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'note'], 'required'],
//            [['project_id'], 'exist', 'targetClass' => '\app\models\project\Project']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'project_id' => 'Project ID',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['project_id' => 'project_id']);
    }

}
