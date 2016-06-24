<?php

namespace app\controllers;

use Yii;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptContractor;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\AllocatedMember;
use app\models\accounting\StagedAllocationSearch;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\DuesAllocation;
use \app\models\member\Member;
use \app\models\accounting\DuesRateFinder;
use yii\helpers\ArrayHelper;
use app\models\accounting\StagedAllocation;


class ReceiptContractorController extends \app\controllers\receipt\BaseController
{
	
	public function actionCreate()
	{
		$model = new ReceiptContractor(['responsible' => new ResponsibleEmployer()]);
		
		if ($model->load(Yii::$app->request->post()) && $model->responsible->load(Yii::$app->request->post())) {
			$model->payor_type = Receipt::PAYOR_CONTRACTOR;
			if ($model->validate() && $model->responsible->validate()) {
				
				$transaction = \Yii::$app->db->beginTransaction();
				try {
					if ($model->save(false)) {
						$model->responsible->receipt_id = $model->id;
						if ($model->responsible->save(false)) {
							// Stage member line items
							if (!empty($model->fee_types)) {
								/* @var $member \app\models\member\Member */
								foreach ($model->responsible->employer->employees as $member) {
									$alloc_memb = new AllocatedMember(['receipt_id' => $model->id, 'member_id' => $member->member_id]);
									if (!$alloc_memb->save())
										throw new \Exception("Error when trying to stage Allocated Member `{$member->member_id}`: {$e}");
									foreach ($model->fee_types as $fee_type) {
										if($fee_type == 'DU') {
											$alloc = new DuesAllocation([
													'alloc_memb_id' => $alloc_memb->id,
													'duesRateFinder' => new DuesRateFinder(
															$member->currentStatus->lob_cd,
															$member->currentClass->rate_class
													),
											]);
											$alloc->allocation_amt = $alloc->estimateAlloc();
										} else {
											$alloc = new AssessmentAllocation([
													'alloc_memb_id' => $alloc_memb->id,
													'allocation_amt' => 0.00,
											]);
										}
										$alloc->fee_type = $fee_type;
										if(!$alloc->save()) {
											// You should not reach here
											$errors = print_r($alloc->errors, true);
											throw new \Exception('Uncaught validation errors: ' . $errors);
										}
									}
								}
							}
							$transaction->commit();
							return $this->redirect([
									'itemize', 
									'id' => $model->id,
									'fee_types' => $model->fee_types,
							]);
						}
							
					}
					$transaction->rollBack();
				} catch (\Exception $e) {
					$transaction->rollBack();
					throw new \Exception('Error when trying to save created Receipt: ' . $e);
				}
			}
					
		}
		return $this->render('create', [
				'model' => $model,
//				'modelResponsible' => $modelResponsible,
		]);
		
	}
	
	public function actionItemize($id, array $fee_types)
	{
		$modelReceipt = $this->findModel($id);
		if(!StagedAllocation::makeTable($id))
			throw new InvalidConfigException('Could not produce staged allocations for: ' . $id);
		$searchAlloc = new StagedAllocationSearch();
		$allocProvider = $searchAlloc->search(Yii::$app->request->queryParams);

        return $this->render('itemize', [
            'modelReceipt' => $modelReceipt,
        	'searchAlloc' => $searchAlloc,
        	'allocProvider' => $allocProvider,
        	'fee_types' => $fee_types,
        ]);
	}
	
    /**
     * Finds the Receipt model based on its primary key value.  Injects responsible employer
     * object
     * 
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ReceiptContractor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
	public function findModel($id) 
	{
        if (($model = ReceiptContractor::findOne($id)) == null) 
            throw new NotFoundHttpException('The requested page does not exist.');
        $model->responsible = ResponsibleEmployer::findOne(['receipt_id' => $id]);
        return $model;
	}
}