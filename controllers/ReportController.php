<?php

namespace app\controllers;

use app\models\accounting\UniversalFile;
use app\models\member\ClassCode;
use app\models\member\Member;
use app\models\report\CredentialForm;
use app\models\report\UniversalFileForm;
use app\models\training\Credential;
use app\models\training\CurrentMemberCredential;
use app\models\training\MemberCompliance;
use app\models\training\MemberCredential;
use app\models\training\MemberCredRespFit;
use app\models\training\Standing;
use Exception;
use Yii;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use app\models\report\ExportCsvForm;
use app\models\member\PacExport;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii2tech\csvgrid\CsvGrid;
use yii\data\ArrayDataProvider;
use PHPExcel_Exception;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;
use PHPExcel_Shared_Date;
use PHPExcel_Style_Color;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Exception;

class ReportController extends Controller
{

	public $layout = 'reporting';

	/**
	 * @return string
     */
	public function actionIndex()
	{

		return $this->render('index');
	}

	public function actionActiveMembers()
    {
        return $this->render('reportico', [
            'folder' => 'memberrpts',
            'report_nm' => 'activemembers',
        ]);
    }

    public function actionInactiveMembers()
    {
        return $this->render('reportico', [
            'folder' => 'memberrpts',
            'report_nm' => 'inactivemembers',
        ]);
    }

    public function actionUnemployedMembers()
    {
        return $this->render('reportico', [
            'folder' => 'memberrpts',
            'report_nm' => 'unemployedmembers',
        ]);
    }

    public function actionPacSummary()
	{
		return $this->render('reportico', [
				'folder' => 'memberrpts',
				'report_nm' => 'pacsummary',
		]);
	}
	
	public function actionNotPac()
	{
		return $this->render('reportico', [
				'folder' => 'memberrpts',
				'report_nm' => 'notinpac',
		]);
	}

    /**
     * @return string
     */
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
					$msg = nl2br("Export successfully generated \n\t--> File Name: $file_nm \n--> Total Record Count: $count \n--> Total Trans Amount: $amount");
	        		Yii::$app->session->addFlash('success', $msg);
				} catch (Exception $e) {
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
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return Yii::$app->response->sendFile($fqdn)->on(Response::EVENT_AFTER_SEND, function($event) {
    			unlink($event->data);
			}, $fqdn);
		}
		Yii::$app->session->setFlash('notice', "File already downloaded.  Please click `Generate Export` to produce another.");
		return $this->redirect(['pac-export']);	
	}

	public function actionPacContributions()
    {
        return $this->render('reportico', [
            'folder' => 'memberrpts',
            'report_nm' => 'paccontributions',
        ]);
    }

	public function actionGlaziers()
	{
		return $this->render('reportico', [
				'folder' => 'memberrpts',
				'report_nm' => 'glaziers',
		]);
	}
	
	public function actionWrongPayor()
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'nonemployee',
		]);
	}
	
	public function actionContractorInfo()
	{
		return $this->render('reportico', [
				'folder' => 'contractorrpts',
				'report_nm' => 'contractorinfo',
		]);
	}

    public function actionHoursSummary($lob_cd)
    {
        return $this->render('reportico', [
            'folder' => 'contractorrpts',
            'report_nm' => 'hourssum' . $lob_cd,
        ]);
    }

    public function actionReceiptsJournal($trade = '')
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'receiptsjournal' . $trade,
		]);
	}
	
	public function actionDuesStatus()
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'duesstatus',
		]);
	}
	
	public function actionInternational()
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'international',
		]);
	}

	public function actionUniversal()
    {
        $model = new UniversalFileForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $query = UniversalFile::find()->where(['acct_month' => $model->acct_month])
                                          ->orderBy([
                                              'B' => SORT_ASC,
                                              'C' => SORT_ASC,
                                              'received_dt' => SORT_ASC,
                                              'hd' => SORT_ASC,
                                              'N' => SORT_ASC,
                                              'O' => SORT_ASC,
                                          ]);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => false,
            ]);
            return $this->renderPartial('universal-template', ['dataProvider' => $dataProvider]);
        }
        return $this->render('universal-criteria', ['model' => $model]);
    }
	
	public function actionPaymentMethod()
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'paymethodsumm',
		]);
	}
	
	/*
	public function actionDelinquentDues()
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'delinquentdues',
		]);
	}
	*/
	
	public function actionCandidateSuspends()
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'candidatesusps',
		]);
	}
	
	public function actionCandidateDrops()
	{
		return $this->render('reportico', [
				'folder' => 'accountingrpts',
				'report_nm' => 'candidatedrops',
		]);
	}

	public function actionYearlyTotals()
    {
        return $this->render('reportico', [
            'folder' => 'accountingrpts',
            'report_nm' => 'yearlytotals',
        ]);

    }

    public function actionTrainingHistory()
    {
        $model = new CredentialForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->option == CredentialForm::OPT_CERTIFICATE)
                return $this->redirect(['/report/certificate', 'member_id' => $model->member_id]);
            elseif ($model->option == CredentialForm::OPT_TRANSFER) {
                $member = Member::findOne($model->member_id);
                if (in_array($member->currentClass->member_class, [ClassCode::CLASS_APPRENTICE, ClassCode::CLASS_HANDLER]))
                    return $this->redirect(['/report/transfer-form', 'member_id' => $model->member_id]);
                Yii::$app->session->addFlash('error', "$member->fullName is not an apprentice");
            }


        }

        return $this->render('training-history', ['model' => $model]);

    }

    /**
     * Build certificate spreadsheet from training credentials
     *
     * Template `TRAINING CERTIFICATION.xltx` has named ranges based on credential_id.  Conditional formatting of
     * expired credentials has to handled here.
     *
     * @param $member_id
     * @return string
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws PHPExcel_Exception
     * @throws ForbiddenHttpException
     * @throws UserException
     */
    public function actionCertificate($member_id)
    {
        if (Yii::$app->user->can('manageTraining')) {
            $member = Member::findOne($member_id);
            $query = MemberCompliance::findMemberCompliance($member_id, 'show_on_cert');
            $credentials = $query->where(['show_on_cert' => 'T'])->all();

            $template_nm = 'TRAINING CERTIFICATION.xltx';
            $file_nm = 'Cert_' . substr($member->report_id, -4);
            $template_path = implode(DIRECTORY_SEPARATOR, [Yii::$app->getRuntimePath(), 'templates', 'xlsx', $template_nm]);

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($template_path);

            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->getCell($objPHPExcel->getNamedRange('full_nm')->getRange())->setValue($member->fullName);
            $sheet->getCell($objPHPExcel->getNamedRange('trade')->getRange())->setValue($member->currentStatus->lob->short_descrip);

            foreach ($credentials as $credential) {
                /* @var $credential MemberCredential */
                $named = $objPHPExcel->getNamedRange('complete_dt' . $credential->credential_id);
                if (isset($named)) {
                    $complete_dt = PHPExcel_Shared_Date::PHPToExcel(strtotime($credential->complete_dt));
                    $range = $named->getRange();
                    $sheet->getCell($range)->setValue(isset($credential->complete_dt) ? $complete_dt : '');
                    if ($credential instanceof MemberCredRespFit) {
                        /* @var $credential MemberCredRespFit */
                        $resp = $credential->memberRespirator;
                        if (isset($resp->complete_dt)) {
                            $range = $objPHPExcel->getNamedRange('brand')->getRange();
                            $sheet->getCell($range)->setValue($resp->brand);
                            $range = $objPHPExcel->getNamedRange('size')->getRange();
                            $sheet->getCell($range)->setValue("$resp->resp_size ($resp->resp_type)");
                        }
                    }
                    $named = $objPHPExcel->getNamedRange('expire_dt' . $credential->credential_id);
                    if (isset($named) && (($range = $named->getRange()) != null)) {
                        if (isset($credential->expire_dt)) {
                            $expire_timestamp = strtotime($credential->expire_dt);
                            $expire_dt = ($expire_timestamp > time()) ? PHPExcel_Shared_Date::PHPToExcel(strtotime($credential->expire_dt)) : 'Expired';
                            $sheet->getCell($range)->setValue($expire_dt);
                            if ($expire_dt == 'Expired') {
                                $this->alertCell($sheet, $range);
                                // Haz/lead includes other credentials in certificate
                                if ($credential->credential_id == Credential::HAZ_LEAD)
                                    // G16 Lead Awareness, G21 Respiratory Protection, G24 Silica Awareness
                                    foreach (['G16', 'G21', 'G24'] as $cell)
                                        $this->alertCell($sheet, $cell);
                            }
                        }
                    }
                    if (isset($credential->schedule_dt)) {
                        $schedule_dt = PHPExcel_Shared_Date::PHPToExcel(strtotime($credential->schedule_dt));
                        $range = $objPHPExcel->getNamedRange('schedule_dt' . $credential->credential_id)->getRange();
                        $sheet->getCell($range)->setValue($schedule_dt);
                    }
                } else {
                    Yii::error("*** MCC100 `$credential->credential_id` has no corresponding range on template. Credential: " . print_r($credential, true));
                    throw new UserException("Problem exporting certificate.  See log for details.  Code `MCC100`");
                }
            }

            /*  START ========> Temporary code to hide either aerial lifts or MEWP *********************************************************/
            $mewp65 = $objPHPExcel->getNamedRange('complete_dt65')->getRange();
            $dt = $sheet->getCell($mewp65)->getValue();
            if (isset($dt)) {
                $this->toggleRowVisibility($sheet, 'complete_dt40');
                $this->toggleRowVisibility($sheet, 'complete_dt64');
            } else {
                $this->toggleRowVisibility($sheet, 'complete_dt65');
                $this->toggleRowVisibility($sheet, 'complete_dt66');
            }
            /*  END  ========> Temporary code to hide either aerial lifts or MEWP **********************************************************/

            // CAS visibility.  If no CAS 1, always show CAS 2.  If CAS 2 exists and is active, hide CAS 1 row, otherwise hide CAS 2 row
            $hide = $member->isCasLevel2() ? Credential::CAS_LEVEL_1 : Credential::CAS_LEVEL_2;
            $this->toggleRowVisibility($sheet, 'complete_dt' . $hide);

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $this->setHeaders($file_nm);

            ob_start();
            $objWriter->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        throw new ForbiddenHttpException("You are not allowed to perform this action ($member_id)");
    }

    /**
     * @param $member_id
     * @return false|string
     * @throws ForbiddenHttpException
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws \yii\db\Exception
     */
    public function actionTransferForm($member_id) {
        if (Yii::$app->user->can('manageTraining')) {
            $member = Member::findOne($member_id);
            $ojt = (new Standing(['member' => $member]))->ojt();
            $query = MemberCompliance::findMemberCompliance($member_id, 'show_on_cert');
            $credentials = $query->where(['show_on_cert' => 'T'])->all();

            $template_nm = 'TRANSFER FORM.xltx';
            $file_nm = 'Txfr_' . $member->imse_id;
            $template_path = implode(DIRECTORY_SEPARATOR, [Yii::$app->getRuntimePath(), 'templates', 'xlsx', $template_nm]);

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($template_path);

            $sheet = $objPHPExcel->getActiveSheet();
            $style = '&"-,Bold"&16';
            $sheet->getHeaderFooter()->setOddHeader('&L' . $style . 'District Council 50 JATTF' . '&12 ' . CHR(10) . '2240 Young St, Honolulu, HI 96826' . '&C' . $style . 'Trade: ' . $ojt['trade'] . '&R' . $style. $ojt['full_nm'] . CHR(10) . 'IUPAT ID: ' . $ojt['iupat_id']);

            $sheet->getCell($objPHPExcel->getNamedRange('indenture_dt')->getRange())->setValue(($ojt['indenture_dt'] <> '') ? PHPExcel_Shared_Date::PHPToExcel(strtotime($ojt['indenture_dt'])) : '');
            $sheet->getCell($objPHPExcel->getNamedRange('clearance_requested')->getRange())->setValue(($ojt['clearance_requested'] <> '') ? PHPExcel_Shared_Date::PHPToExcel(strtotime($ojt['clearance_requested'])) : '');
            $sheet->getCell($objPHPExcel->getNamedRange('wage_rate')->getRange())->setValue($ojt['wage_rate']);
            $sheet->getCell($objPHPExcel->getNamedRange('hours')->getRange())->setValue('Hours - ' . number_format($ojt['hours'], 2));
            $sheet->getCell($objPHPExcel->getNamedRange('classification')->getRange())->setValue($ojt['classification']);
            $sheet->getCell($objPHPExcel->getNamedRange('wage_rate')->getRange())->setValue($ojt['wage_rate']);

            /*  START  ========> Temporary code to hide either aerial lifts or MEWP *********************************************************/
            $lifts = [];
            /*  END  ========> Temporary code to hide either aerial lifts or MEWP **********************************************************/

            $row = 62;
            $skip = $member->isCasLevel2() ? Credential::CAS_LEVEL_1 : Credential::CAS_LEVEL_2;

            /* @var $credential CurrentMemberCredential */
            foreach ($credentials as $credential) {
                if ($credential->credential_id == $skip)
                    continue;
                if (isset($credential->complete_dt)) {

                    /*  START  ========> Temporary code to hide either aerial lifts or MEWP *********************************************************/
                    if ($credential->credential_id == 40 || $credential->credential_id == 64 || $credential->credential_id == 65)
                        $lifts[$credential->credential_id] = $row;
                    /*  END  ========> Temporary code to hide either aerial lifts or MEWP **********************************************************/

                    $sheet->getCell('A' . $row)->setValue(PHPExcel_Shared_Date::PHPToExcel(strtotime($credential->complete_dt)));
                    $sheet->getCell('B' . $row)->setValue($credential->credential);
                    if (isset($credential->expire_dt)) {
                        $sheet->getCell('C' . $row)->setValue((strtotime($credential->expire_dt) < time()) ? PHPExcel_Shared_Date::PHPToExcel(strtotime($credential->expire_dt)) : '');
                        $this->alertCell($sheet, 'C' . $row);
                    }
                    $row++;
                }
            }

            /*  START  ========> Temporary code to hide either aerial lifts or MEWP *********************************************************/
            if (key_exists(65, $lifts)  && key_exists(40, $lifts) && key_exists(64, $lifts)) {
                $sheet->getRowDimension($lifts[40])->setVisible(false);
                $sheet->getRowDimension($lifts[64])->setVisible(false);
            }
            /*  END  ========> Temporary code to hide either aerial lifts or MEWP **********************************************************/

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $this->setHeaders($file_nm);

            ob_start();
            $objWriter->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        throw new ForbiddenHttpException("You are not allowed to perform this action ($member_id)");
}

    public function actionExpiredClasses()
    {
        return $this->render('reportico', [
            'folder' => 'memberrpts',
            'report_nm' => 'expiredcreds',
        ]);

    }

    /**
     * @param PHPExcel_Worksheet $sheet
     * @param $range
     * @throws PHPExcel_Exception
     */
    private function alertCell(PHPExcel_Worksheet $sheet, $range)
    {
        $sheet->getStyle($range)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
        $sheet->getStyle($range)->getFont()->setBold(true);
    }

    /**
     * @param PHPExcel_Worksheet $sheet
     * @param $namedRange
     * @param false $visible
     * @noinspection PhpSameParameterValueInspection
     */
    private function toggleRowVisibility(PHPExcel_Worksheet $sheet, $namedRange, $visible = false)
    {
        $row = strpbrk($sheet->getParent()->getNamedRange($namedRange)->getRange(), '0123456789');
        $sheet->getRowDimension($row)->setVisible($visible);
    }

    /**
     * @param $file_nm
     * @param string $extension
     * @noinspection PhpSameParameterValueInspection
     */
    private function setHeaders($file_nm, $extension = 'xlsx')
    {
        $headers = Yii::$app->getResponse()->getHeaders();
        $headers->set('Cache-Control', 'no-cache');
        $headers->set('Pragma', 'no-cache');
        $headers->set('Content-Type', 'application/force-download');
        $headers->set('Content-Type', "application/$extension;charset=utf-8");
        $headers->set('Content-Disposition', "attachment;filename=$file_nm.$extension");
        $headers->set('Expires', '0');
    }

}