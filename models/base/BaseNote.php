<?php

namespace app\models\base;

use Yii;
use app\models\user\UserRecord;

/**
 * This is the model class for table "MemberNotes".
 *
 * @property integer $id
 * @property string $note
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property UserRecord $author
 */
class BaseNote extends \yii\db\ActiveRecord
{
	protected $_validationRules = []; 
	protected $_labels = [];
	
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
        $common_rules = [
            [['note'], 'required'],
            [['note'], 'string'],
            [['created_at', 'created_by'], 'integer'],
        ];
       return array_merge($this->_validationRules, $common_rules);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $common_labels = [
			'id' => 'ID',
            'note' => 'Note',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
        return array_merge($this->_labels, $common_labels);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(UserRecord::className(), ['id' => 'created_by']);
    }
}
