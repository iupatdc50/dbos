<?php

namespace app\controllers;

use app\helpers\ClassHelper;
use Yii;
use app\models\accounting\DuesAllocation;
use app\models\accounting\AssessmentAllocation;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use app\models\accounting\BaseAllocation;
use app\models\accounting\TradeFeeType;
use app\modules\admin\models\FeeType;

class AllocationController extends Controller
{
    /**
     * @param $alloc_memb_id
     * @return string|\yii\web\Response
     * @throws \Exception
     */
	public function actionCreate($alloc_memb_id)
	{
		$model = new BaseAllocation();
		
		if ($model->load(Yii::$app->request->post())) {
			$model->alloc_memb_id = $alloc_memb_id;
			if ($model->save()) {
				return $this->goBack();
			}
			throw new \Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		// For now, assume that member remittable is the same for all trades 
		$feeOptions = ArrayHelper::map(TradeFeeType::find()->where(['lob_cd' => '1791', 'member_remittable' => 'T'])->orderBy('descrip')->all(), 'fee_type', 'descrip');
		return $this->renderAjax('create', compact('model', 'feeOptions'));
	}

    /**
     * @throws NotFoundHttpException
     * @throws \Exception
     */
	public function actionEditAlloc()
	{
		if(Yii::$app->request->post('hasEditable')) {
			$id = Yii::$app->request->post('editableKey');
			$model = $this->findModel($id);
			$class = (new \ReflectionClass(get_class($model)))->getShortName();

			// $posted is the posted data for StagedAllocation without any indexes
			$posted = current($_POST[$class]);
			// $post is the converted array for single model validation
			$post = [$class => $posted];
			$message = '';
		
			if ($model->load($post)) {

			    /* @var $model BaseAllocation */
			    if (in_array($model->fee_type, $model->statusGenerators) && ($model->allocation_amt != $model->oldAttributes['allocation_amt'])) {
			        $model->backOutMemberStatus();
                    if ($model instanceof DuesAllocation) {
                        /* @var $model DuesAllocation */
                        $model->backOutDuesThru(true);
                    }
                }

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
		
		$alloc_query = BaseAllocation::find();
		$alloc_query->where(['alloc_memb_id' => $_POST['expandRowKey']])
			  		->andWhere(['!=', 'fee_type', FeeType::TYPE_DUES])
			  		->andWhere(['!=', 'fee_type', FeeType::TYPE_HOURS])
			  		;
		$allocProvider = new ActiveDataProvider(['query' => $alloc_query]);
		
		$dues_query = BaseAllocation::find();
		$dues_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => FeeType::TYPE_DUES,
		]);		
		$duesProvider = new ActiveDataProvider(['query' => $dues_query]);
		
		$hrs_query = BaseAllocation::find();
		$hrs_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => FeeType::TYPE_HOURS,
		]);		
		$hrsProvider = new ActiveDataProvider(['query' => $hrs_query]);
		
		return $this->renderAjax('_summary', [
				'allocProvider' => $allocProvider,
				'duesProvider' => $duesProvider,
				'hrsProvider' => $hrsProvider,
		]);
		
	}

	public function actionUpdateGridAjax()
    {
        $alloc_memb_id = $_POST['expandRowKey'];
        $query = BaseAllocation::find()->where(['alloc_memb_id' => $_POST['expandRowKey']])->orderBy('fee_type');
        $allocProvider = new ActiveDataProvider(['query' => $query]);

        return $this->renderAjax('../receipt/_allocgrid', [
            'allocProvider' => $allocProvider,
            'alloc_memb_id' => $alloc_memb_id,
        ]);
    }
	
	public function actionDetailAjax()
	{
		$model = DuesAllocation::findOne(['id' => $_POST['expandRowKey']]);
		return $this->renderAjax('_detail', ['duesProvider' => $model]);
	}

    /**
     * Deletes an existing ActiveRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\db\StaleObjectException
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
	    $model = BaseAllocation::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new yii\web\NotFoundHttpException('The requested page does not exist.');
		}
		return $model;
	}
	
	
}
