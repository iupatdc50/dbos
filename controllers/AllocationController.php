<?php

namespace app\controllers;

use Yii;
use app\models\accounting\DuesAllocation;
use app\models\accounting\AssessmentAllocation;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use app\models\accounting\BaseAllocation;
use app\models\accounting\app\models\accounting;
use app\models\accounting\TradeFeeType;

class AllocationController extends Controller
{
	
	public function actionCreate($alloc_memb_id)
	{
		$model = new BaseAllocation();
		
		if ($model->load(Yii::$app->request->post())) {
			$model->alloc_memb_id = $alloc_memb_id;
			if ($model->save()) {
				return $this->goBack();
			}
			throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		// For now, assume that member remittable is the same for all trades 
		$feeOptions = ArrayHelper::map(TradeFeeType::find()->where(['lob_cd' => '1791', 'member_remittable' => 'T'])->orderBy('descrip')->all(), 'fee_type', 'descrip');
		return $this->renderAjax('create', compact('model', 'feeOptions'));
	}
	
	public function actionEditAlloc()
	{
		if(Yii::$app->request->post('hasEditable')) {
			$id = Yii::$app->request->post('editableKey');
			$model = $this->findModel($id);

			$out = Json::encode(['output'=>'', 'message'=>'']);
			// $posted is the posted data for StagedAllocation without any indexes
			$posted = current($_POST['BaseAllocation']);
			// $post is the converted array for single model validation
			$post = ['BaseAllocation' => $posted];
			$message = '';
		
			if ($model->load($post)) {					
				if ($model->save()) {
			
					$output = Yii::$app->formatter->asDecimal($model->allocation_amt, 2);
					$out = Json::encode(['output' => $output, 'message' => $message]);
					
					echo $out;
					return;
				}
			}
			throw new \Exception ('Problem with post. Errors: ' . print_r($model->errors, true));
		}
		
	}
	
	public function actionSummaryAjax()
	{
		
		$alloc_query = AssessmentAllocation::find();
		$alloc_query->where(['alloc_memb_id' => $_POST['expandRowKey']])
			  		->andWhere(['!=', 'fee_type', 'DU'])
			  		->andWhere(['!=', 'fee_type', 'HR'])
			  		;
		$allocProvider = new ActiveDataProvider(['query' => $alloc_query]);
		
		$dues_query = AssessmentAllocation::find();
		$dues_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => 'DU',
		]);		
		$duesProvider = new ActiveDataProvider(['query' => $dues_query]);
		
		$hrs_query = AssessmentAllocation::find();
		$hrs_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => 'HR',
		]);		
		$hrsProvider = new ActiveDataProvider(['query' => $hrs_query]);
		
		return $this->renderAjax('_summary', [
				'allocProvider' => $allocProvider,
				'duesProvider' => $duesProvider,
				'hrsProvider' => $hrsProvider,
		]);
		
	}
	
	/**
	 * Deletes an existing ActiveRecord model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();
	
		return $this->goBack();
	}
	
	/**
	 * Finds the ActiveRecord model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return \yii\db\ActiveRecord the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		$model = BaseAllocation::findOne($id);
		if (!$model) {
			throw new yii\web\NotFoundHttpException('The requested page does not exist.');
		}
		return $model;
	}
	
	
}
