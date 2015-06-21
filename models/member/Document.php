<?php

namespace app\models\member;

use Yii;
use app\models\value\DocumentType;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MemberDocuments".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $doc_type
 * @property string $doc_id
 * 
 * @property DocumentType $documentType
 */
class Document extends \yii\db\ActiveRecord
{
	/*
	 * Injected Member object, used for creating new entries
	 */
	public $member;
	
	/**
	 * @var mixed	Stages document to be uploaded
	 */
	public $doc_file;
	
	/**
	 * Handles all the document attachment processing functions for the model
	 * 
	 * @see \yii\base\Component::behaviors()
	 */
	public function behaviors()
	{
		return [
				\app\components\behaviors\OpImageBehavior::className(),
		];
	}

	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberDocuments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['doc_type', 'doc_id'], 'required'],
            [['doc_type'], 'exist', 'targetClass' => '\app\models\value\DocumentType'],
            [['doc_id'], 'string', 'max' => 20],
        	[['doc_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'pdf, png'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'doc_type' => 'Type',
            'doc_id' => 'Doc ID',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
	    	if (!(isset($this->member) && ($this->member instanceof Member)))
	    		throw new InvalidConfigException('No member object injected');
    		if ($insert) 
    			$this->member_id = $this->member->member_id;
    		return true;
    	}
    	return false;
    }
    
    public function getTypeOptions()
    {
    	return ArrayHelper::map($this->member->unfiledDocs, 'doc_type', 'doc_type');
    }
    
    
}
