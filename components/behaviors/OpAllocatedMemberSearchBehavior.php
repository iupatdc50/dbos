<?php

namespace app\components\behaviors;

use Yii;
use yii\data\ActiveDataProvider;
use yii\base\Behavior;
use app\helpers\CriteriaHelper;
use yii\base\InvalidParamException;
use app\models\member\Member;

class OpAllocatedMemberSearchBehavior extends Behavior
{
	/**
	 * @var string Name of class to be searched
	 */
	public $recordClass;

	public function search($params)
	{
		$query = call_user_func([$this->recordClass, 'find']);
		$query->joinWith(['member']);
		$query->where(['receipt_id' => $this->owner->receipt_id]);
		
		$m = Member::tableName();
		
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'sort' => ['defaultOrder' => ['fullName' => SORT_ASC]],
				'pagination' => ['pageSize' => 12],
		]);
		
		$dataProvider->sort->attributes['fullName'] = [
				'asc' => [$m.'.last_nm' => SORT_ASC, $m.'.first_nm' => SORT_ASC],
				'desc' => [$m.'.last_nm' => SORT_DESC, $m.'.first_nm' => SORT_DESC],
		];
		$dataProvider->sort->attributes['reportId'] = [
				'asc' => [$m.'.report_id' => SORT_ASC],
				'desc' => [$m.'.report_id' => SORT_DESC],
		];
		
/*		$this->owner->load($params);
		
		if (!$this->owner->validate()) { */
		if (!($this->owner->load($params) && $this->owner->validate())) {
			// uncomment the following line if you do not want to any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}
		
		
		$query->andFilterWhere(['or', ['like', $m . '.last_nm', $this->owner->fullName], ['like', $m . '.first_nm', $this->owner->fullName]])
			->andFilterWhere(['like', $m.'.report_id', $this->owner->reportId])
		;
		return $dataProvider;
		
		
	}
	
	
}