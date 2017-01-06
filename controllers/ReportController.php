<?php

namespace app\controllers;

use Yii;
use \yii\web\Controller;
use yii\web\Response;
use app\models\report\BaseSettingsForm;
use app\models\report\DateSettingsForm;
use app\models\report\DuesStatusForm;

class ReportController extends Controller
{

	public $layout = 'reporting';

	/**
	 * @return mixed
	 */
	public function actionIndex()
	{

		return $this->render('index');
	}
	
	public function actionContractorInfo()
	{
		$model = new BaseSettingsForm;
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
				
		}
		
		return $this->render('contractor-info', ['model' => $model]);
	}
	
	public function actionReceiptsJournal()
	{
		$model = new DateSettingsForm;
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			
		}
		$model->show_islands = false;
		return $this->render('receipts-journal', ['model' => $model]);
	}
	
	public function actionDuesStatus()
	{
		$model = new DuesStatusForm;
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			die(var_dump($model));
		}
		
		return $this->render('dues-status', ['model' => $model]);
	}
	
}