<?php

namespace app\controllers;

use app\models\training\CredCategory;
use app\models\training\Credential;
use app\models\training\DrugTestResult;
use app\models\training\MemberCredential;
use app\models\training\MemberCompliance;
use app\models\training\MemberCredRespFit;
use app\models\training\MemberRespirator;
use InvalidArgumentException;
use PHPExcel_Exception;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;
use PHPExcel_Shared_Date;
use PHPExcel_Style_Color;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Exception;
use Yii;
use app\models\member\Member;
use yii\base\UserException;
use yii\bootstrap\ActiveForm;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class MemberCredentialController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @param $member_id
     * @param $catg
     * @return array|string|Response
     * @throws yii\db\Exception
     */
    public function actionCreate($member_id, $catg)
    {
        if (($member = Member::findOne($member_id)) == null)
            throw new InvalidArgumentException('Invalid member ID passed: ' . $member_id);

        $model = new MemberCredential([
            'member' => $member,
            'catg' => $catg,
        ]);

        $modelResp = new MemberRespirator([
            'brand' => '3M',
            'resp_size' => 'M',
            'resp_type' => MemberRespirator::HALF_FACE,
        ]);

        $modelDrug = new DrugTestResult([
            'test_result' => DrugTestResult::NEGATIVE,
        ]);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Because member ID is unchanged, it gets cleared on load
            /* @var $image UploadedFile */
            $image = $model->uploadImage();
            if ($model->save()) {

                if ($image !== false) {
                    $path = $model->imagePath;
                    $image->saveAs($path);
                }

                Yii::$app->session->addFlash('success', "{$model->credential->credential} entry created");

                if (($model->credential_id == Credential::RESP_FIT) && ($modelResp->load(Yii::$app->request->post()))) {
                    $modelResp->member_id = $model->member_id;
                    $modelResp->credential_id = $model->credential_id;
                    $modelResp->complete_dt = $model->complete_dt;
                    if (!$modelResp->validate() || !$modelResp->save())
                        throw new Exception ('Problem with post.  Errors: ' . print_r($modelResp->errors, true));

                } elseif (($model->credential_id == Credential::DRUG) && ($modelDrug->load(Yii::$app->request->post()))) {
                    $modelDrug->member_id = $model->member_id;
                    $modelDrug->credential_id = $model->credential_id;
                    $modelDrug->complete_dt = $model->complete_dt;
                    if (!$modelDrug->validate() || !$modelDrug->save())
                        throw new Exception ('Problem with post.  Errors: ' . print_r($modelDrug->errors, true));
                }

                return $this->goBack();
            }
            throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
        }
        return $this->renderAjax('create', compact('model', 'modelResp', 'modelDrug'));

    }

    public function actionAttach($id)
    {
        $model = MemberCredential::findOne($id);
        $oldPath = $model->imagePath;
        $oldId = $model->doc_id;

        if ($model->load(Yii::$app->request->post())) {
            $image = $model->uploadImage();

            if($image === false)
                $model->doc_id = $oldId;

            if	($model->save()) {
                if ($image !== false) {
                    if (file_exists($oldPath))
                        unlink($oldPath);
                    $path = $model->imagePath;
                    /* @var $image UploadedFile */
                    $image->saveAs($path);
                }

                Yii::$app->session->addFlash('success', "Document successfully uploaded");
                return $this->goBack();
            }
        }
        return $this->renderAjax('attach', ['model' => $model]);

    }

    /**
     * @param $id
     * @return string
     * @throws ForbiddenHttpException
     */
	public function actionCompliance($id)
    {
        function getProvider($member_id, $catg)
        {
            $query = MemberCompliance::findMemberComplianceByCatg($member_id, $catg);
            $provider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['display_seq' => SORT_ASC]],
                'pagination' => false,
            ]);
            return $provider;
        }

        if (!Yii::$app->user->can('browseTraining'))
            throw new ForbiddenHttpException("You are not allowed to perform this action ({$id})");

        Yii::$app->user->returnUrl = Yii::$app->request->url;

        $member = Member::findOne($id);

        return $this->render('compliance', [
            'member' => $member,
            'recurProvider' => getProvider($id, CredCategory::CATG_RECURRING),
            'nonrecurProvider' => getProvider($id, CredCategory::CATG_NONRECUR),
            'medtestsProvider' => getProvider($id, CredCategory::CATG_MEDTESTS),
            'coreProvider' => getProvider($id, CredCategory::CATG_CORE),
        ]);

    }

    public function actionHistoryAjax()
    {
        $curr = MemberCredential::findOne($_POST['expandRowKey']);

        $query = MemberCredential::find()->where(['and',
            ['member_id' => $curr->member_id],
            ['credential_id' => $curr->credential_id],
            ['<>', 'id', $curr->id],
        ])->orderBy(['complete_dt' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $cred = Credential::findOne($curr->credential_id);
        $show_attach = ($cred->catg == CredCategory::CATG_MEDTESTS);

        return $this->renderAjax('_history', [
            'dataProvider' => $dataProvider,
            'credential_id' => $curr->credential_id,
            'show_attach' => $show_attach,
        ]);
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
            $extension = 'xlsx';
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
                            $sheet->getCell($range)->setValue("{$resp->resp_size} ({$resp->resp_type})");
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
                                    foreach (['G14', 'G17', 'G20'] as $cell)
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
                    Yii::error("*** MCC100 `{$credential->credential_id}` has no corresponding range on template. Credential: " . print_r($credential, true));
                    throw new UserException("Problem exporting certificate.  See log for details.  Code `MCC100`");
                }
            }

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $headers = Yii::$app->getResponse()->getHeaders();
            $headers->set('Cache-Control', 'no-cache');
            $headers->set('Pragma', 'no-cache');
            $headers->set('Content-Type', 'application/force-download');
            $headers->set('Content-Type', "application/{$extension};charset=utf-8");
            $headers->set('Content-Disposition', "attachment;filename={$file_nm}.{$extension}");
            $headers->set('Expires', '0');

            ob_start();
            $objWriter->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        throw new ForbiddenHttpException("You are not allowed to perform this action ({$member_id})");
    }

    /**
     * Uses SQL DELETE to trigger promotion of previous entry
     *
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        if (Yii::$app->user->can('manageTraining')) {
            $credential = MemberCredential::findOne($id);
            $command = Yii::$app->db->createCommand("DELETE FROM MemberCredentials WHERE `id` = :id;", [':id' => $id]);
            try {
                $command->execute();
                Yii::$app->session->setFlash('success', "Credential entry deleted. Previous entry promoted");
                if(isset($credential->doc_id) && (!$credential->deleteImage()))
                    Yii::$app->session->setFlash('error', "Could not delete document `{$credential->doc_id}`");
            } catch  (Exception $e) {
                Yii::$app->session->setFlash('error', "Problem with post.  See log for details. Code `MCC110`");
                Yii::error("*** MCC110 Delete MemberCredential {$id} failed. Errors: " . print_r($e->errorInfo, true));
            }
            return $this->goBack();
        }
        throw new ForbiddenHttpException("You are not allowed to perform this action ({$id})");

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

}