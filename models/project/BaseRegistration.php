<?php

namespace app\models\project;

use Yii;
use app\models\contractor\Contractor;
use app\models\project\jtp\Project;

/**
 * This is the model class for table "Registrations".
 *
 * @property integer $id
 * @property string $project_id
 * @property string $bid_dt
 * @property string $bidder
 * @property integer $estimated_hrs
 * @property string $doc_id
 *
 * @property Contractor $biddingContractor
 * @property Project $project
 */
class BaseRegistration extends \yii\db\ActiveRecord
{
	protected $_validationRules = [];
	protected $_labels = [];
	
	/**
	 * @var mixed	Stages document to be uploaded
	 */
	public $doc_file;
	
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Registrations';
    }

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
            [['bid_dt', 'bidder'], 'required'],
            [['bid_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['estimated_hrs'], 'integer'],
        	[['bidder'], 'exist', 'targetClass' => '\app\models\contractor\Contractor', 'targetAttribute' => 'license_nbr'],
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
        $common_labels =  [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'bid_dt' => 'Bid Date',
            'bidder' => 'Bidder',
            'estimated_hrs' => 'Hours',
            'doc_id' => 'Doc',
        ];
        return array_merge($this->_labels, $common_labels);
    }
    
    public function getIsAwarded()
    {
    	return $this->hasOne(AwardedBid::className(), ['registration_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBiddingContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'bidder']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
    	return $this->hasOne(Project::className(), ['project_id' => 'project_id']);
    }


    public function getHourRange()
    {
    	return isset($this->estimated_hrs_to) ? $this->estimated_hrs . '-' . $this->estimated_hrs_to : $this->estimated_hrs;
    }
    
    public function getAmountRange()
    {
    	return isset($this->estimate_to) ? $this->estimate . '-' . $this->estimate_to : $this->estimate;
    }
    
}
