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
use yii\helpers\Json;
use app\models\accounting\StagedAllocation;
use app\models\accounting\AllocationBuilder;
use app\models\accounting\app\models\accounting;

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
									$builder = new AllocationBuilder();
									$result = $builder->prepareAllocs($alloc_memb, $model->fee_types);
									if ($result != true)
										throw new \Exception('Uncaught validation errors: ' . $result);
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
		]);
		
	}
	
	public function actionItemize($id, array $fee_types)
	{
		$this->storeReturnUrl();
		$modelReceipt = $this->findModel($id);
		if(!StagedAllocation::makeTable($id))
			throw new InvalidConfigException('Could not produce staged allocations for: ' . $id);
		$searchAlloc = new StagedAllocationSearch(['fee_types' => $fee_types]);
		$allocProvider = $searchAlloc->search(Yii::$app->request->queryParams);
		
		//Ajax updateables
		if(Yii::$app->request->post('hasEditable')) {
			$alloc_memb_id = Yii::$app->request->post('editableKey');
			$modelAlloc = StagedAllocation::findOne($alloc_memb_id);
			$modelAlloc->fee_types = $fee_types;
			$out = Json::encode(['output'=>'', 'message'=>'']);
			// $posted is the posted data for StagedAllocation without any indexes
			$posted = current($_POST['StagedAllocation']);
			// $post is the converted array for single model validation
			$post = ['StagedAllocation' => $posted];
			
			if ($modelAlloc->load($post)) {
				$modelAlloc->save();
				$output = $message = '';
				foreach ($fee_types as $fee_type) {
					if (isset($posted[$fee_type])) {
						$output = Yii::$app->formatter->asDecimal($modelAlloc->$fee_type, 2);
//						$message = $fee_type . ' was changed';
					}
				}
				$out = Json::encode(['output' => $output, 'message' => $message]);
			}			
			echo $out;
			return;
		}

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
	
	/**
	 * Allows GoBack() to return to the sending page instead of the home page
	 */
	protected function storeReturnUrl()
	{
		Yii::$app->user->returnUrl = Yii::$app->request->url;
	}
	
}