<?php

namespace app\controllers;

use Yii;
use app\models\accounting\DuesAllocation;
use app\models\accounting\AssessmentAllocation;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;

class AllocationController extends Controller
{

	public function actionSummaryJson()
	{
		
		$alloc_query = AssessmentAllocation::find();
		$alloc_query->where(['alloc_memb_id' => $_POST['expandRowKey']])
			  		->andWhere(['!=', 'fee_type', 'DU'])
			  		->andWhere(['!=', 'fee_type', 'HR'])
			  		;
		$allocProvider = new ActiveDataProvider(['query' => $alloc_query]);
		
		$dues_query = AssessmentAllocation::find();
		$dues_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => 'DU',
		]);		
		$duesProvider = new ActiveDataProvider(['query' => $dues_query]);
		
		$hrs_query = AssessmentAllocation::find();
		$hrs_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => 'HR',
		]);		
		$hrsProvider = new ActiveDataProvider(['query' => $hrs_query]);
		
		return $this->renderAjax('_summary', [
				'allocProvider' => $allocProvider,
				'duesProvider' => $duesProvider,
				'hrsProvider' => $hrsProvider,
		]);
		
	}
	
}
