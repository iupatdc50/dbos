<?php

namespace app\controllers;

use app\controllers\base\SubmodelController;
use Yii;
use app\models\accounting\ReceiptContractor;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\AllocatedMember;
use app\models\accounting\AllocationBuilder;

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
