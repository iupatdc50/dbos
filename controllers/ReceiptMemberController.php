<?php

namespace app\controllers;

use Yii;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMember;
use app\models\accounting\AllocatedMember;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\DuesAllocation;
use \app\models\member\Member;
use \app\models\accounting\DuesRateFinder;
use yii\helpers\ArrayHelper;
use app\models\accounting\app\models\accounting;

class ReceiptMemberController extends \app\controllers\receipt\BaseController
{
	public function actionCreate()
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
}