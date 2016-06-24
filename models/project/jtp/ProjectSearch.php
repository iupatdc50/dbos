<?php

namespace app\models\project\jtp;

use Yii;
use yii\base\Model;
use app\models\project\jtp\Project;

/**
 * ProjectSearch represents the model behind the search form about `app\models\project\jtp\Project`.
 */
class ProjectSearch extends Project
{
	public $hold;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_status', 'project_nm', 'general_contractor', 'agreement_type', 
            		'disposition', 'awarded_contractor', 'hold'], 'safe'],
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
     * Builds search data provider
     *
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
    	return [
    		[
    			'class' => \app\components\behaviors\OpProjectSearchBehavior::className(),
    			'recordClass' => 'app\models\project\jtp\Project',
    			'join_with' => 'holdAmount',
    			'sort_attrs' => [['model_attr' => 'hold', 'sort_col' => 'hold_amt']],
    			'search_attrs' => [['op' => 'mixed', 'col' => 'hold_amt', 'val' => 'hold']],
    		],
    	];
    }
    
}
