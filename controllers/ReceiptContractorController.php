<?php

namespace app\controllers;

use app\models\accounting\BillPayment;
use Throwable;
use Yii;
use app\controllers\receipt\MultiMemberController;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptContractor;
// use app\models\accounting\ReceiptContractorSearch;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\RemittanceExcel;
use app\models\member\Member;
use app\models\accounting\AllocationBuilder;
use yii\data\SqlDataProvider;
use yii\db\Exception;
use yii\web\Response;

class ReceiptContractorController extends MultiMemberController
{

    /**
     * @param $lob_cd
     * @param null $id
     * @return string|Response
     * @throws \Exception
     * @throws Throwable
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
				
				$transaction = Yii::$app->db->beginTransaction();
				try {
					if ($model->save(false)) {
						$model->responsible->receipt_id = $model->id;
						if ($model->responsible->save(false)) {
							
							$file = $model->uploadFile();
							
							// Stage member line items
							if ($file == false) { // manual entry
                                if ($model->populate) {

                                    foreach ($model->responsible->employer->employees as $member) {
                                        if ($member->currentStatus->lob_cd == $lob_cd) {
                                            $builder = new AllocationBuilder();
                                            $alloc_memb = $builder->prepareAllocMemb($model->id, $member->member_id);
                                            if ($alloc_memb != false) {
                                                $result = $builder->prepareAllocs($alloc_memb, $model->fee_types);
                                                if ($result != true)
                                                    throw new \Exception('Uncaught validation errors: ' . $result);
                                            }
                                        }
                                    }
                                } else {

                                    $session = Yii::$app->session;
                                    $session['prebuild'] = 'bypass';

                                }
							} else { // uploaded spreadsheet exists 
								$path = $model->filePath;
								$file->saveAs($path);
								$remittance = new RemittanceExcel(['xlsx_file' => $model->filePath]);
								$allocs = $remittance->setFeeColumns($model->fee_types)->getAllocsArray();
								foreach ($allocs as $alloc) {
									$member = Member::findOne([
											'report_id' => $alloc['report_id'],
											'last_nm' => $alloc['last_nm'],
											'first_nm' => $alloc['first_nm'],
									]);
									if (!isset($member)) {
									    Yii::$app->session->addFlash('error', "Receipt $model->id member `{$alloc['report_id']}` not found.  Skipping row.");
										Yii::warning("Receipt $model->id member `{$alloc['report_id']}` not found.  Skipping row.");
										continue;
									}
                                    $builder = new AllocationBuilder();
									$alloc_memb = $builder->prepareAllocMemb($model->id, $member->member_id);
									if ($alloc_memb != false) {
                                        $result = $builder->prepareAllocsFromArray($alloc_memb, $alloc);
                                        if ($result != true)
                                            throw new \Exception('Uncaught validation errors: ' . $result);
                                        if ($alloc_memb->allocationCount == 0)
                                            $alloc_memb->delete();
                                    }
								}
								if ($remittance->getDocNumber() > 0)
								    $model->addBillPayment(new BillPayment(['bill_id' => $remittance->getDocNumber()]));
								else  // for transmittals produced before 1.6.6 r 250
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
		
		return $this->render('create', [
				'model' => $model,
		]);
		
	}

	/*
    public function actionSummaryJson($id)
	{
    	if (!Yii::$app->user->can('browseReceipt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));

        $searchModel = new ReceiptContractorSearch();
        $searchModel->license_nbr = $id;
        /** @noinspection PhpUndefinedMethodInspection */
    /*
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//			$dataProvider->pagination = ['pageSize' => 8];
        return $this->asJson($this->renderAjax('_summary', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
        ]));

	}
    */

    /**
     * @param $license_nbr
     * @return Response
     * @throws Exception
     */
	public function actionSummFlattenedJson($license_nbr)
    {
        if (!Yii::$app->user->can('browseReceipt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));

        $typesSubmitted = ReceiptContractor::getFeeTypesSubmitted($license_nbr);

        /** @noinspection SqlResolve */
        $count = Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM ResponsibleEmployers AS E JOIN Receipts AS R ON E.receipt_id = R.`id` WHERE R.void = 'F' AND E.license_nbr = :license_nbr ",
            [':license_nbr' => $license_nbr]
        )->queryScalar();

        $sqlProvider = new SqlDataProvider([
            'sql' => ReceiptContractor::getFlattenedReceiptsByContractorSql($typesSubmitted),
            'params' => [':license_nbr' => $license_nbr],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->asJson($this->renderAjax('_summflattened', [
            'sqlProvider' => $sqlProvider,
            'typesSubmitted' => $typesSubmitted,
        ]));

    }


}