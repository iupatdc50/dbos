<?php

namespace app\models\contractor;

use Yii;

/**
 * This is the model class for both agreements tables
 *
 * @property integer $id
 * @property string $license_nbr
 * @property string $signed_dt
 * @property string $term_dt
 * @property string $doc_id
 *
 * @property Contractor $contractor
 */
class Agreement extends \yii\db\ActiveRecord
{
	protected $_validationRules = []; 
	protected $_labels = [];
	
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
    public function rules()
    {
        $common_rules = [
            [['signed_dt'], 'required'],
            [['signed_dt', 'term_dt'], 'date', 'format' => 'php:Y-m-d'],
        	[['term_dt'], 'default'],
        	[['doc_id'], 'string', 'max' => 20],
        	[['doc_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'pdf, png'],
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
            'license_nbr' => 'License Nbr',
            'signed_dt' => 'Signed',
            'term_dt' => 'Terminated',
        	'doc_id' => 'Doc',
        ];
        return array_merge($this->_labels, $common_labels);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }
        
    
}
