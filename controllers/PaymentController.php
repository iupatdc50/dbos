<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\project\jtp\Payment;
use app\models\project\jtp\Project;
use app\helpers\OptionHelper;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
{
	public $recordClass = '';
	public $relationAttribute = 'project_id';
	
	public function behaviors()
	{
		return [
				'verbs' => [
						'class' => VerbFilter::className(),
						'actions' => [
								'delete' => ['post'],
						],
				],
				'access' => [
						'class' => AccessControl::className(),
						'only' => ['create', 'delete'],
						'rules' => [
								[
										'allow' => true,
										'actions' => ['create', 'delete'],
										'roles' => ['manageProject'],
								],
						],
				],
				
		];
	}
	
	/**
	 * Creates a new ActiveRecord model.
	 *
	 * Assumes that all submodel creates are ajax
	 *
	 * @return mixed
	 */
	public function actionCreate($relation_id)
	{
		/* @var Payment $model */
		$model = new Payment;
	
		if ($model->load(Yii::$app->request->post())) {
			$model->project_id = $relation_id;
			$success = false;
			if ($model->save()) {
				$success = true;
				if ($model->close_project) {
					// Setting close date will also change status in payment model
					$model->project->close_dt = $model->payment_dt;
					if (!$model->project->save())
						$success = false;
				}
				if ($success)
					return $this->goBack();
			}
			throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		return $this->renderAjax('create', compact('model'));
	
	}
	
	/**
	 * Deletes an existing ActiveRecord model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		$model = Payment::findOne($id);
		if (!$model) 
			throw new NotFoundHttpException('The requested page does not exist.');
		$model->delete();
	
		return $this->goBack();
	}
	
	
}
