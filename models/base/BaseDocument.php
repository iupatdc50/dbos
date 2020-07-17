<?php

namespace app\models\base;

use app\components\behaviors\OpImageBehavior;
use app\models\member\Member;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Class BaseDocument
 * @package app\models\base
 *
 * @property string $imagePath
 * @method UploadedFile uploadImage()
 * @method boolean deleteImage()
 */
abstract class BaseDocument extends ActiveRecord
{
	 // Injected Member object, used for creating new entries
	/* @var Member $member */
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
				OpImageBehavior::className(),
		];
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
        	[['doc_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'pdf, png, jpg, jpeg'],
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

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidConfigException
     */
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

}
