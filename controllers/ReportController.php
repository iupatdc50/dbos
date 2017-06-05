<?php

namespace app\controllers;

use Yii;
use \yii\web\Controller;
use yii\web\Response;
use app\models\report\ExportCsvForm;

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
	
	public function actionPacSummary()
	{
		return $this->render('pac-summary');
	}
	
	public function actionNotPac()
	{
		return $this->render('not-pac');
	}
	
	public function actionPacExport()
	{
		$model = new ExportCsvForm;
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
				
		}
		$model->show_islands = false;
		if (!isset($model->delimiter))
			$model->delimiter = ExportCsvForm::CELL_TILDE;
		if (!isset($model->enclosure))
			$model->enclosure = ExportCsvForm::ENCLOSE_NONE;
		return $this->render('pac-export', ['model' => $model]);
		
	}
	
	public function actionGlaziers()
	{
		return $this->render('glaziers');
	}
	
	public function actionContractorInfo()
	{
		return $this->render('contractor-info');
	}
	
	public function actionReceiptsJournal()
	{

	}
	
	public function actionDuesStatus()
	{
		return $this->render('dues-status');
	}
	
}