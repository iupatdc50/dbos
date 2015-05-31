<?php

namespace app\models\project\lma;

use Yii;
use yii\base\Model;
use app\models\project\lma\Project;

/**
 * ProjectSearch represents the model behind the search form about `app\models\project\lma\Project`.
 */
class ProjectSearch extends Project
{
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_status', 'project_nm', 'general_contractor', 'agreement_type', 
            		'disposition', 'awarded_contractor', 'is_maint'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    
    /**
     * Builds serach data provider
     *
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
    	return [
    			[
    					'class' => \app\components\behaviors\OpProjectSearchBehavior::className(),
    					'recordClass' => 'app\models\project\lma\Project',
    					'search_attrs' => [['op' => 'equal', 'col' => 'is_maint', 'val' => 'is_maint']],
    			],
    	];
    }
    
    
}
