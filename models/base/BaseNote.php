<?php

namespace app\models\base;

use Yii;
use app\models\user\User;

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
 */
class BaseNote extends \yii\db\ActiveRecord
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
				['class' => \yii\behaviors\TimestampBehavior::className(), 'updatedAtAttribute' => false],
				['class' => \yii\behaviors\BlameableBehavior::className(), 'updatedByAttribute' => false],
				\app\components\behaviors\OpImageBehavior::className(),
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
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
