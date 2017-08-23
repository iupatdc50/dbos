<?php

namespace app\controllers;

use Yii;
use \yii\web\Controller;
use yii\web\Response;
use app\models\report\ExportCsvForm;
use app\models\member\PacExport;
use yii2tech\csvgrid\CsvGrid;
use yii\data\ArrayDataProvider;
use app\models\accounting\ReceiptFlattenedAlloc;

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
		$path = Yii::getAlias('@webroot') . Yii::$app->params['tempDir'];
		$fqdn = false;
		if ($model->load(Yii::$app->request->post())) {
			if ($model->validate()) {
				$cols = [];
				for ($i = 1; $i <= 36; $i++) {
					$attr = 'field_' . sprintf('%02d', $i);
					if ($i == 10 || $i == 16)
						$cols[] = ['attribute' => $attr, 'format' => 'raw'];
					elseif ($i == 33)
						$cols[] = ['attribute' => $attr, 'format' => 'decimal'];
					else 
						$cols[] = $attr;
				}
				$criteria = [
						'begin_dt' => $model->begin_dt->getMySqlDate(),
						'end_dt' => $model->end_dt->getMySqlDate(),
						'lob_cd' => $model->lob_cd,
				];
				try {
					$content = PacExport::findByCriteria($criteria);
					$count = count($content);
					$amount = money_format('%i', PacExport::sumContribution($criteria));
					$exporter = new CsvGrid([
							'dataProvider' => new ArrayDataProvider(['allModels' => $content, 'pagination' => false]),
							'columns' => $cols,
							'showHeader' => false,
						    'csvFileConfig' => [
						        'cellDelimiter' => $model->delimiter,
						        'enclosure' => $model->enclosure,
    						],
							
					]);
					$file_nm = "pac_export_{$model->lob_cd}_{$model->begin_dt->getTimestamp()}.txt";
					$fqdn = $path . $file_nm;
					$exporter->export()->saveAs($fqdn); 
					$msg = nl2br("Export successfully generated \n\t--> File Name: {$file_nm} \n--> Total Record Count: {$count} \n--> Total Trans Amount: {$amount}");
	        		Yii::$app->session->addFlash('success', $msg);
				} catch (\Exception $e) {
					Yii::error("*** RC100  Export error (Messages: " . print_r($e, true));
					Yii::$app->session->addFlash('error', 'Export failed. Check log for details. Code `RC100`');
				}
			} else {
				Yii::error("*** RC105  Export criteria error (Messages: " . print_r($model->errors, true));
				Yii::$app->session->addFlash('error', 'Problem with criteria. Check log for details. Code `RC105`');
			}
		} 
		$model->show_islands = false;
		if (!isset($model->delimiter))
			$model->delimiter = ExportCsvForm::CELL_TILDE;
		if (!isset($model->enclosure))
			$model->enclosure = ExportCsvForm::ENCLOSE_NONE;
		return $this->render('pac-export', ['model' => $model, 'fqdn' => $fqdn]);
		
	}
	
	public function actionDownload($fqdn)
	{
		if(file_exists($fqdn)) {
			return Yii::$app->response->sendFile($fqdn)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
    			unlink($event->data);
			}, $fqdn);
		}
		Yii::$app->session->setFlash('notice', "File already downloaded.  Please click `Generate Export` to produce another.");
		return $this->redirect(['pac-export']);	
	}
	
	public function actionGlaziers()
	{
		return $this->render('glaziers');
	}
	
	public function actionContractorInfo()
	{
		return $this->render('contractor-info');
	}
	
	/**
	 * 
	 * @param string $trade  Should be either 1889 or blank
	 * @return Ambigous <string, string>
	 */
	public function actionReceiptsJournal($trade = '')
	{
		return $this->render('receipts-journal', ['trade' => $trade]);
	}
	
	public function actionDuesStatus()
	{
		return $this->render('dues-status');
	}
	
	public function actionDelinquentDues()
	{
		return $this->render('delinquent-dues');
	}
	
	public function actionCandidateSuspends()
	{
		return $this->render('candidate-suspends');
	}
	
	public function actionCandidateDrops()
	{
		return $this->render('candidate-drops');
	}
	
}