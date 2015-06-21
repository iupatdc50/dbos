<?php

namespace app\models\value;

use Yii;

/**
 * This is the model class for table "DocumentTypes".
 *
 * @property string $doc_type
 * @property string $catg
 */
class DocumentType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DocumentTypes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['doc_type', 'catg'], 'required'],
            [['doc_type'], 'string', 'max' => 50],
            [['catg'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'doc_type' => 'Doc Type',
            'catg' => 'Catg',
        ];
    }
}
