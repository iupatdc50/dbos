<?php

namespace app\controllers;

use Yii;
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
	
	public $summOrder = 'project_nm asc';
	public $summPageSize = 15;
	
	/**
	 * Provide summary of active special projects for a bidder.  If $awarded_only param
	 * is passed, it is retained for the session and assumed subsequent calls.
	 * 
	 * @param string $id Bidder's license number
	 * @param boolean $awarded_only True means show only projects awarded to bidder
	 */
	public function actionSummaryJson($id, $awarded_only = null)
	{
    	if (!Yii::$app->user->can('browseProject')) {
    		echo Json::encode($this->renderAjax('/partials/_deniedview'));
    	} else {
			if (isset($awarded_only)) 
				Yii::$app->session['awarded_only'] = $awarded_only;
			elseif (isset(Yii::$app->session['awarded_only'])) 
				$awarded_only = Yii::$app->session['awarded_only'];
			else  // Parameter not passed or previously saved
				$awarded_only = true;
			
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
		
			echo Json::encode($this->renderAjax('_summary', ['dataProvider' => $dataProvider, 'id' => $id, 'awarded_only' => $awarded_only]));
    	}
	}
	
}