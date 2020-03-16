<?php

namespace app\models\base;

use app\components\behaviors\OpImageBehavior;
use app\models\user\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the base model class for all note tables.
 *
 * @property integer $id
 * @property string $note
 * @property string $doc_id
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property User $author
 * @property string $imagePath
 */
class BaseNote extends ActiveRecord
{
	protected $_validationRules = []; 
	protected $_labels = [];
	
	/**
	 * @var mixed	Stages document to be uploaded
	 */
	public $doc_file;
	
	public function behaviors()
	{
		return [
				['class' => TimestampBehavior::className(), 'updatedAtAttribute' => false],
				['class' => BlameableBehavior::className(), 'updatedByAttribute' => false],
				OpImageBehavior::className(),
		];
	}

	/**
     * @inheritdoc
     */
    public function rules()
    {
        $common_rules = [
            [['note'], 'required'],
            [['note', 'doc_id'], 'string'],
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
        	'doc_id' => 'Doc ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
        return array_merge($this->_labels, $common_labels);
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
