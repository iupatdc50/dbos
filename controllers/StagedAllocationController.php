<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\db\Exception;
use kartik\grid\EditableColumnAction;
use app\controllers\base\SubmodelController;
use app\models\accounting\ReceiptContractor;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\AllocatedMember;
use app\models\accounting\AllocationBuilder;
use app\models\accounting\BaseAllocation;
use app\models\accounting\DuesAllocation;
use app\models\accounting\StagedAllocation;
use app\models\accounting\TradeFeeType;
use app\modules\admin\models\FeeType;
use app\models\accounting\AssessmentAllocation;

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
//	public function actionAdd($receipt_id, array $fee_types)
	public function actionAdd($receipt_id)
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
//				$result = $builder->prepareAllocs($alloc_memb, $fee_types);
				$result = $builder->prepareAllocs($alloc_memb, $receipt->feeTypesArray);
				if ($result)
					return $this->goBack();
			}
			throw new Exception	('Problem with post.  Errors: ' . print_r($builder->errors, true));
		}
		return $this->renderAjax('add', compact('model', 'license_nbr'));
	
	}
	
	public function actionAddType($receipt_id)
	{
		
		/** @var ReceiptContractor $receipt */
		$receipt = $this->findReceiptModel($receipt_id);		
				
		/** @var ActiveRecord $model */
		$model = new \app\models\accounting\AddTypeForm([
				'fee_types' => $receipt->feeTypesArray,
		]);
		
		if ($model->load(Yii::$app->request->post())) {
			foreach ($receipt->members as $alloc_memb) {
				$builder = new AllocationBuilder();
				if (!$builder->prepareAllocs($alloc_memb, [$model->new_fee_type]))
					throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));;
			}
			$fee_types[] = $model->new_fee_type;
			return $this->redirect([
					'/receipt-contractor/itemize',
					'id' => $receipt_id,
					'fee_types' => $fee_types,
			]);
				
		}
		return $this->renderAjax('add-type', compact('model'));
	}
	
	/**
	 * Provides a filtered list of available Fee Types
	 * 
	 * Note that format of the JSON out must be built from an array in this format: 
	 * 		['id' => $key, 'name' => $value]
	 * in order to work with a Select2
	 */
	public function actionFeeType()
	{
		$out = [];
		if (isset($_POST['depdrop_parents']) && !empty($_POST['depdrop_params'])) {
			$parents = $_POST['depdrop_parents'];
			if ($parents != null) {
				$lob_cd = $parents[0];
				$fee_types = $_POST['depdrop_params'][0];
				$trade_types = TradeFeeType::find()->where(['lob_cd' => $lob_cd, 'employer_remittable' => 'T'])->orderBy('descrip')->all();
				foreach($trade_types as $row) {
					if (strpos($fee_types, $row['fee_type']) === false)
					    $out[] = ['id' => $row['fee_type'], 'name' => $row['descrip']];
				}
				echo Json::encode(['output' => $out, 'selected' => '']);
				return;
			}
		}
		echo Json::encode(['output' => '', 'selected' => '']);
	}
	
	public function actionReassign($id)
	{
		$model = $this->findModel($id);
		/** @var ReceiptContractor $receipt */
		$receipt = $this->findReceiptModel($model->receipt_id);
		$license_nbr = $receipt->responsible->license_nbr;
		
		if ($model->load(Yii::$app->request->post())) {
			$alloc_memb = AllocatedMember::findOne($model->alloc_memb_id);
			$alloc_memb->member_id = $model->member_id;
			if (!$alloc_memb->save())
				throw new Exception	('Problem with post.  Errors: ' . print_r($alloc_memb->errors, true));
			
			$this->goBack();
			
		}
		
		return $this->renderAjax('reassign', compact('model', 'license_nbr'));
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
				if($model->save()) {
					// Apply to underlying allocation
					$base = $this->findBaseAlloc($model->alloc_memb_id, $attr);
					$base->allocation_amt = $posted[$attr];
					$base->save();
				}
				$output = Yii::$app->formatter->asDecimal($model->$attr, 2);
				$out = Json::encode(['output' => $output, 'message' => $message]);
			}
			echo $out;
			return;
		}
		
	}
	
	public function actionDelete($id)
	{
		function deleteAllocs($allocs)
		{
			$result = true;
			foreach ($allocs as $alloc)
				if (!$alloc->delete()) {
					Yii::$app->session->addFlash('error', 'Could not remove allocation.  Check log for details. Code `SAC010`');
					Yii::error("*** SAC010 Allocation delete error.  Allocation: " . print_r($alloc, true));
					$result = false;
				}
			return $result;
		}
		if (deleteAllocs(DuesAllocation::findAll(['alloc_memb_id' => $id, 'fee_type' => FeeType::TYPE_DUES]))) {
			// Assume dues allocations already gone
		   	if (deleteAllocs(BaseAllocation::findAll(['alloc_memb_id' => $id]))) {
				$alloc_memb = AllocatedMember::findOne($id);
				if ($alloc_memb->delete()) {
					parent::actionDelete($id);
				} else {
					Yii::$app->session->addFlash('error', 'Could not complete allocation remove.  Check log for details. Code `SAC020`');
					Yii::error("*** SAC020 AllocatedMember delete error.  Allocation: " . print_r($alloc_memb, true));
				} 
		   	}
		}
		
		return $this->goBack();
		
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
	
	/**
	 * Retrieves the underlying (normalized) base allocation
	 * 
	 * @param int $alloc_memb_id  	
	 * @param string $fee_type		This can be obtained from the post's column key in the edit-alloc action 
	 * @throws \InvalidArgumentException
	 * @return Ambigous <BaseAllocation, NULL>
	 */
	protected function findBaseAlloc($alloc_memb_id, $fee_type)
	{
		$alloc = BaseAllocation::findOne(['alloc_memb_id' => $alloc_memb_id, 'fee_type' => $fee_type]);
		if (!$alloc)
			throw new \InvalidArgumentException('Attemtping to access a non-existent allocation');
		return $alloc;
	}
	
	
}
