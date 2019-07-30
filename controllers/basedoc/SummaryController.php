<?php

namespace app\controllers\basedoc;

use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

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
    public $summOrder = 'signed_dt desc';
    
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

        /** @noinspection MissedViewInspection */
		return $this->asJson($this->renderAjax('_summary', ['dataProvider' => $dataProvider, 'id' => $id]));
	}

	
	
}