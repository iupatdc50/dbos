<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use app\models\accounting\StagedAllocation;
use app\models\member\Member;

/**
 * StagedAllocationSearch represents the model behind the search form about `app\models\accounting\StagedAllocation`.
 */
class StagedAllocationSearch extends StagedAllocation
{
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['alloc_memb_id'], 'integer'],
				[['member_id', 'classification', 'fullName', 'reportId'], 'safe'],
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
		    	'recordClass' => 'app\models\accounting\StagedAllocation',
		    ],
		];
	}
	
}
