<?php

namespace app\models\training;

use app\models\base\BaseDocument;
use app\models\value\DocumentType;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "TrainingDocuments".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $doc_type
 * @property string $doc_id
 * 
 */
class Document extends BaseDocument
{
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TrainingDocuments';
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getTypeOptions()
    {
    	return ArrayHelper::map($this->member->getUnfiledDocs(DocumentType::CATG_TRAINING), 'doc_type', 'doc_type');
    }

}
