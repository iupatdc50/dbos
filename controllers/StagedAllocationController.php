<?php

namespace app\controllers;

use Yii;
use yii\helpers\Json;
use yii\db\Exception;
use app\controllers\base\SubmodelController;
use app\models\accounting\AddTypeForm;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMultiMember;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\AllocatedMember;
use app\models\accounting\AllocationBuilder;
use app\models\accounting\BaseAllocation;
use app\models\accounting\DuesAllocation;
use app\models\accounting\StagedAllocation;
use app\models\accounting\TradeFeeType;
use app\modules\admin\models\FeeType;

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
     * @param $receipt_id
     * @return mixed
     * @throws Exception
     * @throws Yii\base\InvalidConfigException
     */
	public function actionAdd($receipt_id)
	{
		/** @var ReceiptMultiMember $receipt */
		$receipt = $this->findReceiptModel($receipt_id);		
//		$license_nbr = $receipt->responsible->license_nbr;
		$lob_cd = $receipt->lob_cd;

		/** @var StagedAllocation $model */
		$model = new $this->recordClass;
	
		if ($model->load(Yii::$app->request->post())) {
            $builder = new AllocationBuilder();
			$alloc_memb = $builder->prepareAllocMemb($receipt_id, $model->member_id);
			if ($alloc_memb == false)
                throw new Exception	('Problem with post.  Errors: ' . print_r($alloc_memb->errors, true));
            $result = $builder->prepareAllocsFromModel($model, $alloc_memb->id);
            if ($result) {
                Yii::$app->session->addFlash('success', 'Member allocations added');
                return $this->goBack();
            } else {
                Yii::$app->session->addFlash('error', 'Could not add allocation.  Check log for details. Code `SAC010`');
                Yii::error("*** SAC010 PrepareAllocs error.  StagedAlloc: " . print_r($model, true));
			}

		}
		return $this->renderAjax('add', compact('model', 'lob_cd'));
	
	}

    /**
     * @param $receipt_id
     * @return string|\yii\web\Response
     * @throws Exception
     * @throws Yii\base\InvalidConfigException
     */
    public function actionAddType($receipt_id)
	{
		
		/** @var ReceiptMultiMember $receipt */
		$receipt = $this->findReceiptModel($receipt_id);		
				
		$model = new AddTypeForm([
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

    /**
     * Edits Ajax updateable amount columns staged allocation grid
     *
     * @throws Exception
     * @throws \yii\web\NotFoundHttpException
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
                    /** @var BaseAllocation $base */
					$base = $this->getBaseAlloc($model->alloc_memb_id, $attr);
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

    /**
     * @param int $id
     * @return mixed|\yii\web\Response
     * @throws \yii\db\StaleObjectException
     */
	public function actionDelete($id)
	{
		function deleteAllocs($allocs)
		{
			$result = true;
			foreach ($allocs as $alloc)
			    /** @var BaseAllocation $alloc */
                /** @noinspection PhpUnhandledExceptionInspection */
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

    /**
     * @param $id
     * @return Receipt
     * @throws Yii\base\InvalidConfigException
     */
	protected function findReceiptModel($id)
	{
		$receipt = Receipt::findOne($id);
        if (!$receipt)
        	throw new \InvalidArgumentException('Attemtping to access a non-existent receipt: ' . $id);
        /* @var $receipt ReceiptMultiMember */
        $receipt->responsible = ResponsibleEmployer::findOne($id);
        if (!$receipt->responsible)
        	throw new Yii\base\InvalidConfigException('Contractor receipt does not have an associated responsible contrator');
        return $receipt;
	}
	
	/**
	 * Retrieves the underlying (normalized) base allocation.  If it doesn't exist, a new one is created.
	 * 
	 * @param int $alloc_memb_id  	
	 * @param string $fee_type		This can be obtained from the post's column key in the edit-alloc action 
	 * @throws \yii\db\Exception
	 * @return BaseAllocation
	 */
	protected function getBaseAlloc($alloc_memb_id, $fee_type)
	{
		$alloc = BaseAllocation::findOne(['alloc_memb_id' => $alloc_memb_id, 'fee_type' => $fee_type]);
		if (!$alloc) {
            $alloc = new BaseAllocation([
                'alloc_memb_id' => $alloc_memb_id,
                'fee_type' => $fee_type,
                'allocation_amt' => 0.00,
            ]);
            if (!$alloc->save())
                /** @noinspection PhpUnhandledExceptionInspection */
                throw new Exception('Unable to save base allocation');
        }
		return $alloc;
	}
	
	
}
