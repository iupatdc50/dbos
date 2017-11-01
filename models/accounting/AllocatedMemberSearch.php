<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\accounting\AllocatedMember;
use app\models\member\Member;

/**
 * AllocatedMemberSearch represents the model behind the search form about `app\models\accounting\AllocatedMember`.
 */
class AllocatedMemberSearch extends AllocatedMember
{
	//Search place holders
	public $reportId;
	public $fullName;
	public $classification;
	
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['id'], 'integer'],
				[['receipt_id', 'member_id', 'classification', 'fullName', 'reportId'], 'safe'],
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
		    	'class' => \app\components\behaviors\OpAllocatedMemberSearchBehavior::className(),
		    	'recordClass' => 'app\models\accounting\AllocatedMember',
		    ],
		];
	}
	
}
