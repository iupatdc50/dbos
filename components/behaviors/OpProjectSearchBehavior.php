<?php

namespace app\components\behaviors;

use Yii;
use yii\data\ActiveDataProvider;
use yii\base\Behavior;
use app\helpers\CriteriaHelper;
use yii\base\InvalidParamException;

class OpProjectSearchBehavior extends Behavior
{
	/**
	 * @var string Name of class to be searched
	 */
	public $recordClass;
	
	/**
	 * @var array [[$model_attr, $sort_col]]
	 */
	public $sort_attrs = [];

	/**
	 * @var array
	 */
	public $search_attrs = [];
	
	/**
	 * @var string|array Other models to include in the data provider join
	 */
	public $join_with;
	
	public $awarded_contractor;
	
	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$query = call_user_func([$this->recordClass, 'find']);
	
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'sort' => ['defaultOrder' => ['project_nm' => SORT_ASC]],
		]);
	
		if (count($this->sort_attrs) > 0) {
			foreach ($this->sort_attrs as $attr) {
				$dataProvider->sort->attributes[$attr['model_attr']] = [
						'asc' => [$attr['sort_col'] => SORT_ASC], 
						'desc' => [$attr['sort_col'] => SORT_DESC],
				];
			}
		}
//		$dataProvider->sort->attributes['hold'] = ['asc' => ['hold_amt' => SORT_ASC], 'desc' => ['hold_amt' => SORT_DESC]];
		$dataProvider->sort->attributes['awarded_contractor'] = ['asc' => ['contractor' => SORT_ASC], 'desc' => ['contractor' => SORT_DESC]];
	
		// Initiatize agreement_type (hidden column)
		$query->andFilterWhere(['agreement_type' => $this->owner->type_filter]);
	
		// Default set to active
		if (!isset($params['ProjectSearch']['project_status']))
			$params['ProjectSearch']['project_status'] = 'A';
	
		if (!($this->owner->load($params) && $this->owner->validate())) {
			// uncomment the following line if you do not want to any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}
	
		$query->joinWith(['awarded.registration.biddingContractor']);
		if (isset($this->join_with))
			$query->joinWith($this->join_with);
	
		$query->andFilterWhere(['project_status' => $this->owner->project_status])
			->andFilterWhere(['like', 'project_nm', $this->owner->project_nm])
			->andFilterWhere(['like', 'general_contractor', $this->owner->general_contractor])
			->andFilterWhere(['like', 'disposition', $this->owner->disposition])
			->andFilterWhere(['like', 'contractor', $this->awarded_contractor])
		;
	
		if (count($this->search_attrs) > 0) {
			foreach ($this->search_attrs as $attr) {
//				die(print_r($attr['op']));
				switch ($attr['op']) {
					case "equal":
						$criteria = [$attr['col'] => $this->owner->$attr['val']];
						break;
					case "like":
						$criteria = ['like', $attr['col'], $this->owner->$attr['val']];
						break;
					case "mixed":
						$criteria = CriteriaHelper::parseMixed($attr['col'], $this->owner->$attr['val']);
						break;
					default:
						throw new InvalidParamException("Invalid criteria operator `{$attr['op']}` passed in parameter");
				}
				$query->andFilterWhere($criteria);
				
			}
		}
		return $dataProvider;
		
	}
	
	
}