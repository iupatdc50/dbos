<?php

namespace app\controllers;

use app\controllers\basedoc\SubmodelController;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;

/**
 * Special summary controller for general Registration models by bidder.
 */
class RegistrationController extends SubmodelController
{

	/* Base controller properties - All are required */
	public $recordClass = 'app\models\project\BaseRegistration';
	public $relationAttribute = 'bidder';
	
	public $summOrder = 'bid_dt desc';
	public $summPageSize = 15;
	
	public function actionSummaryJson($id, $awarded_only = true)
	{
		$query = call_user_func([$this->recordClass, 'find'])
					->where([$this->relationAttribute => $id])
					->andWhere(['project_status' => 'A'])
					->joinWith(['project', 'isAwarded'])
					->orderBy($this->summOrder);
	
		if ($awarded_only)
			$query->andWhere(['not', ['start_dt' => null]]);
			
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => ['pageSize' => $this->summPageSize],
				'sort' => false,
		]);
	
		echo Json::encode($this->renderAjax('_summary', ['dataProvider' => $dataProvider, 'id' => $id]));
	}
	
}