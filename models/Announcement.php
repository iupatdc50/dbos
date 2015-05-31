<?php

namespace app\models;

use Yii;
use app\models\user\UserRecord;

/**
 * This is the model class for table "Announcements".
 *
 * @property integer $id
 * @property string $note
 * @property integer $created_at
 * @property integer $created_by
 */
class Announcement extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Announcements';
    }

	public function behaviors()
	{
		return [
				['class' => \yii\behaviors\TimestampBehavior::className(), 'updatedAtAttribute' => false],
				['class' => \yii\behaviors\BlameableBehavior::className(), 'updatedByAttribute' => false],
		];
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['note'], 'required'],
            [['note'], 'string'],
            [['created_at', 'created_by'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'note' => 'Note',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(UserRecord::className(), ['id' => 'created_by']);
    }

}
