<?php

namespace app\models\accounting;

use app\components\behaviors\OpImageBehavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Class Document
 *
 * @property integer $id
 * @property integer $receipt_id
 * @property string $doc_type
 * @property string $doc_id
 * @property string $imagePath
 *
 * @property array $typeOptions
 *
 * @method UploadedFile uploadImage()
 * @method boolean deleteImage()
 */
class Document extends ActiveRecord
{
	 // Injected Receipt object, used for creating new entries
	/* @var Receipt $receipt */
	public $receipt;

	/**
	 * @var mixed	Stages document to be uploaded
	 */
	public $doc_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ReceiptDocuments';
    }

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
            'receipt_id' => 'Receipt ID',
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
	    	if (!(isset($this->receipt) && ($this->receipt instanceof Receipt)))
	    		throw new InvalidConfigException('No receipt object injected');
    		if ($insert) 
    			$this->receipt_id = $this->receipt->id;
    		return true;
    	}
    	return false;
    }

    public function getTypeOptions()
    {
        return ArrayHelper::map($this->receipt->unfiledDocs, 'doc_type', 'doc_type');
    }

}
