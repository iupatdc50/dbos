<?php

namespace app\controllers\base;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\Json;
use Yii\web\Response;

/**
 * Standard extension to process JSON calls
 *
 * Assumes a partial called _summary can be rendered
 * 
 */
class SummaryController extends SubmodelController
{
	
    /** @var array Other query qualifiers */
    public $summWhere = []; 
    /** @var array Joined models */
    public $summJoinWith = [];
	/** @var string Order of active data provider */
    public $summOrder = 'effective_dt desc';
    /** @var array Other parameters to pass to view */
    public $viewParams = [];
    
    public $summPageSize = 5;
	
	public function actionSummaryJson($id)
	{
        /** @var ActiveQuery $query */
        $query = call_user_func([$this->recordClass, 'find'])
					->where([$this->relationAttribute => $id])
					->orderBy($this->summOrder);

		if (!empty($this->summJoinWith))
			$query->joinWith($this->summJoinWith);

		foreach ($this->summWhere as $col => $val)
			$query->andWhere([$col => $val]);

		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => ['pageSize' => $this->summPageSize],
				'sort' => false,
		]);

		$params = array_merge(['dataProvider' => $dataProvider, 'id' => $id], $this->viewParams);

        Yii::$app->response->format = Response::FORMAT_JSON;
        /** @noinspection MissedViewInspection */
        echo Json::encode($this->renderAjax('_summary', $params));
	}
	
}
