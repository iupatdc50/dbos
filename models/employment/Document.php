<?php

namespace app\models\employment;

use app\components\behaviors\OpImageBehavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "EmploymentDocuments".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $effective_dt
 * @property string $doc_type
 * @property string $doc_id
 * @property string $test_result
 *
 * @property string $imagePath
 * @property array $typeOptions
 * @property array $resultOptions
 * @property string $extendedType
 *
 * @method UploadedFile uploadImage()
 * @method boolean deleteImage()
 */
class Document extends ActiveRecord
{
    const POSITIVE = 'POSITIVE';
    const NEGATIVE = 'NEGATIVE';

    // Injected Employment object, used for creating new entries
    /* @var Employment $employment */
    public $employment;

    /**
     * @var mixed	Stages document to be uploaded
     */
    public $doc_file;

    public static function tableName()
    {
        return 'EmploymentDocuments';
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
            [['test_result'], 'in', 'range' => [self::POSITIVE, self::NEGATIVE]],
            ['test_result', 'default'],
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
            'effective_dt' => 'Effective',
            'doc_type' => 'Type',
            'doc_id' => 'Doc ID',
            'test_result' => 'Result',
            'extendedType' => 'Type',
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
            if (!(isset($this->employment) && ($this->employment instanceof Employment)))
                throw new InvalidConfigException('No employment object injected');
            if ($insert) {
                $this->member_id = $this->employment->member_id;
                $this->effective_dt = $this->employment->effective_dt;
            }
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
    	return ArrayHelper::map($this->employment->unfiledDocs, 'doc_type', 'doc_type');
    }

    public function getResultOptions()
    {
        return [self::NEGATIVE => self::NEGATIVE, self::POSITIVE => self::POSITIVE];
    }

    public function getExtendedType()
    {
        $ext = ($this->test_result != null) ? " [{$this->test_result}]" : '';
        return $this->doc_type . $ext;
    }

}
