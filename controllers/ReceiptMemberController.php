<?php

namespace app\controllers;

use Yii;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMember;
use app\models\accounting\ReceiptMemberSearch;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\DuesAllocation;
use \app\models\member\Member;
use \app\models\accounting\DuesRateFinder;
use yii\helpers\ArrayHelper;

class ReceiptMemberController extends \app\controllers\receipt\BaseController
{
	public function actionCreate($id = null)
	{
		$modelReceipt = new ReceiptMember();
		$modelMember = new AllocatedMember();
		$modelsAllocation = [new BaseAllocation];
		
		return $this->render('create', [
				'modelReceipt' => $modelReceipt,
				'modelMember' => $modelMember,
				'modelsAllocation' => (empty($modelsAllocation)) ? [new BaseAllocation] : $modelsAllocation,
		]);
		
	}
	
	public function actionSummaryAjax($id)
	{
		$searchModel = new ReceiptMemberSearch();
		$searchModel->member_id = $id;
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		return $this->renderAjax('_summary', [
				'dataProvider' => $dataProvider,
				'searchModel' => $searchModel,
				'payorPicklist' => Receipt::getPayorOptions(),
		]);
	}
	
}