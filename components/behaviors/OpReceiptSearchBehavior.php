<?php

namespace app\components\behaviors;

use Yii;
use yii\data\ActiveDataProvider;
use yii\base\Behavior;
use app\helpers\CriteriaHelper;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptFeeType;
use yii\base\InvalidParamException;

class OpReceiptSearchBehavior extends Behavior
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
		$query->joinWith(['receipt', 'receipt.feeTypes']);
		
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'sort' => ['defaultOrder' => ['received_dt' => SORT_ASC]],
		]);
	
		if (isset($this->sort_attrs) && (count($this->sort_attrs) > 0)) {
			foreach ($this->sort_attrs as $attr) {
				$dataProvider->sort->attributes[$attr['model_attr']] = [
						'asc' => [$attr['sort_col'] => SORT_ASC], 
						'desc' => [$attr['sort_col'] => SORT_DESC],
				];
			}
		}

		$dataProvider->sort->attributes['received_dt'] = [
				'asc' => [Receipt::tableName() . '.received_dt' => SORT_ASC],
				'desc' => [Receipt::tableName() . '.received_dt' => SORT_DESC],
		];
		
		$this->owner->load($params);
		
		if (!$this->owner->validate()) {
			// uncomment the following line if you do not want to any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}
	
		if (isset($this->join_with))
			$query->joinWith($this->join_with);

		$query->andFilterWhere([
				$this->owner->tableName() . '.receipt_id' => $this->owner->receipt_id,
				Receipt::tableName() . '.received_dt' => $this->owner->received_dt,
		]);
		
		$query->andFilterWhere(['like', ReceiptFeeType::tableName() . '.fee_type', $this->owner->feeTypes])
		;
		
		if (count($this->search_attrs) > 0) {
			foreach ($this->search_attrs as $attr) {
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