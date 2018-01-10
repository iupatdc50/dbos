<?php

namespace app\controllers;

use Yii;
use app\controllers\receipt\BaseController;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptContractor;
use app\models\accounting\ReceiptContractorSearch;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\RemittanceExcel;
use app\models\accounting\AllocatedMember;
use app\models\accounting\AllocatedMemberSearch;
use app\models\accounting\StagedAllocationSearch;
use app\models\member\Member;
use app\models\accounting\StagedAllocation;
use app\models\accounting\AllocationBuilder;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class ReceiptContractorController extends BaseController
{

    /**
     * Displays a single Receipt model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
    	$model = $this->findModel($id);
    	
    	if($model->outOfBalance != 0.00) 
			return $this->redirect([
					'itemize', 
					'id' => $model->id,
					'fee_types' => $model->feeTypesArray,
			]);
    		    	    	
    	$searchMemb = new AllocatedMemberSearch(['receipt_id' => $id]);
    	$membProvider = $searchMemb->search(Yii::$app->request->queryParams);

    	return $this->render('view', compact('model', 'membProvider', 'searchMemb'));
    }

    /**
     * @param $lob_cd
     * @param null $id
     * @return string|\yii\web\Response
     * @throws \Exception
     * @throws \yii\db\Exception
     */
	public function actionCreate($lob_cd, $id = null)
	{
		$model = new ReceiptContractor([
				'responsible' => new ResponsibleEmployer(['license_nbr' => $id]),
				'scenario' => Receipt::SCENARIO_CREATE,
				'lob_cd' => $lob_cd,
		]);
		
		if ($model->load(Yii::$app->request->post()) && $model->responsible->load(Yii::$app->request->post())) {
			
			$model->payor_type = Receipt::PAYOR_CONTRACTOR;
			if ($model->validate() && $model->responsible->validate()) {
				
				$transaction = \Yii::$app->db->beginTransaction();
				try {
					if ($model->save(false)) {
						$model->responsible->receipt_id = $model->id;
						if ($model->responsible->save(false)) {
							
							$file = $model->uploadFile();
							
							// Stage member line items
							if ($file == false) { // manual entry
								/* @var $member \app\models\member\Member */
								foreach ($model->responsible->employer->employees as $member) {
								    if ($member->currentStatus->lob_cd == $lob_cd) {
                                        $alloc_memb = new AllocatedMember(['receipt_id' => $model->id, 'member_id' => $member->member_id]);
                                        if (!$alloc_memb->save())
                                            throw new \Exception("Error when trying to stage Allocated Member `{$member->member_id}`: {$alloc_memb->errors}");
                                        $builder = new AllocationBuilder();
                                        $result = $builder->prepareAllocs($alloc_memb, $model->fee_types);
                                        if ($result != true)
                                            throw new \Exception('Uncaught validation errors: ' . $result);
                                    }
								}
							} else { // uploaded spreadsheet exists 
								$path = $model->filePath;
								$file->saveAs($path);
								$remittance = new RemittanceExcel(['xlsx_file' => $model->filePath]);
								$allocs = $remittance->setFeeColumns($model->fee_types)->allocsArray;
								foreach ($allocs as $alloc) {
									$member = Member::findOne([
											'report_id' => $alloc['report_id'],
											'last_nm' => $alloc['last_nm'],
											'first_nm' => $alloc['first_nm'],
									]);
									if (!isset($member)) {
										Yii::warning("Receipt {$model->id} member `{$alloc->report_id}` not found.  Skipping row.");
										continue;
									}
									$alloc_memb = new AllocatedMember(['receipt_id' => $model->id, 'member_id' => $member->member_id]);
									if (!$alloc_memb->save())
										throw new \Exception("Error when trying to stage Allocated Member `{$member->member_id}`: {$alloc_memb->errors}");
									$builder = new AllocationBuilder();
									$result = $builder->prepareAllocsFromArray($alloc_memb, $alloc);
									if ($result != true)
										throw new \Exception('Uncaught validation errors: ' . $result);
								}
								$model->deleteUploadedFile();
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
		
		$this->initCreate($model);
		
		return $this->render('create', [
				'model' => $model,
		]);
		
	}

    /**
     * @param $id
     * @param array $fee_types
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionItemize($id, array $fee_types = [])
	{
		$this->storeReturnUrl();
		$modelReceipt = $this->findModel($id);
		StagedAllocation::makeTable($id);
		$searchAlloc = new StagedAllocationSearch(['receipt_id' => $id, 'fee_types' => $fee_types]);
		$allocProvider = $searchAlloc->search(Yii::$app->request->queryParams);
		
        return $this->render('itemize', [
            'modelReceipt' => $modelReceipt,
        	'searchAlloc' => $searchAlloc,
        	'allocProvider' => $allocProvider,
//        	'fee_types' => $fee_types,
        ]);
	}
	
	public function actionSummaryJson($id)
	{
    	if (!Yii::$app->user->can('browseReceipt')) {
    		echo Json::encode($this->renderAjax('/partials/_deniedview'));
    	} else {
			$searchModel = new ReceiptContractorSearch();
			$searchModel->license_nbr = $id;
			$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
			echo Json::encode($this->renderPartial('_summary', [
					'dataProvider' => $dataProvider,
					'searchModel' => $searchModel, 
					'id' => $id,
			]));
		}
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