<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use kartik\grid\EditableColumnAction;
use app\controllers\base\SubmodelController;
use app\models\accounting\ReceiptContractor;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\AllocatedMember;
use app\models\accounting\AllocationBuilder;
use app\models\accounting\StagedAllocation;

/**
 * StagedAllocationController implements the CRUD actions for accouting\StagedAllocation model.
 */
class StagedAllocationController extends SubmodelController
{
	public $recordClass = 'app\models\accounting\StagedAllocation';
	public $relationAttribute = 'memb_alloc_id';
	
	/**
	 * Creates a new ActiveRecord model.
	 *
	 * Specialized create includes new allocated member row
	 *
	 * @return mixed
	 */
	public function actionAdd($receipt_id, array $fee_types)
	{
		/** @var ReceiptContractor $receipt */
		$receipt = $this->findReceiptModel($receipt_id);		
		$license_nbr = $receipt->responsible->license_nbr;
		
		/** @var ActiveRecord $model */
		$model = new $this->recordClass;
	
		if ($model->load(Yii::$app->request->post())) {
			$alloc_memb = new AllocatedMember(['receipt_id' => $receipt_id, 'member_id' => $model->member_id]);
			if ($alloc_memb->save()) {
				$builder = new AllocationBuilder();
				$result = $builder->prepareAllocs($alloc_memb, $fee_types);
				if ($result)
					return $this->goBack();
			}
			throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		return $this->renderAjax('add', compact('model', 'license_nbr'));
	
	}	
	
	/**
	 * Edits Ajax updateable amount columns staged allocation grid
	 */
	public function actionEditAlloc()
	{
		if(Yii::$app->request->post('hasEditable')) {
			$id = Yii::$app->request->post('editableKey');
			$model = $this->findModel($id);
			// Assume only 1 allocation column updated at a time
			$attr = key(current($_POST['StagedAllocation']));
			// Make column update safe in model
			$model->fee_types = [$attr];
			$out = Json::encode(['output'=>'', 'message'=>'']);
			// $posted is the posted data for StagedAllocation without any indexes
			$posted = current($_POST['StagedAllocation']);
			// $post is the converted array for single model validation
			$post = ['StagedAllocation' => $posted];
			$message = '';
				
			if ($model->load($post)) {
				$model->save();
				$output = Yii::$app->formatter->asDecimal($model->$attr, 2);
				$out = Json::encode(['output' => $output, 'message' => $message]);
			}
			echo $out;
			return;
		}
		
	}
	
	protected function findReceiptModel($id)
	{
		$receipt = ReceiptContractor::findOne($id);
        if (!$receipt)
        	throw new \InvalidArgumentException('Attemtping to access a non-existent receipt: ' . $id);
        $receipt->responsible = ResponsibleEmployer::findOne($id);
        if (!$receipt->responsible)
        	throw new InvalidConfigException('Contractor receipt does not have an associated responsible contrator');
        return $receipt;
	}
	
	
}
