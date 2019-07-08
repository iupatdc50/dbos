<?php

namespace app\controllers;

use app\models\training\CredCategory;
use app\models\training\Credential;
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
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

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
     * @return string|Response
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

        if ($model->load(Yii::$app->request->post())) {
            // Because member ID is unchanged, it gets cleared on load
            if ($model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->addFlash('success', "{$model->credential->credential} entry created");
                    if (($model->credential_id == Credential::RESP_FIT) && ($modelResp->load(Yii::$app->request->post()))) {
                        $modelResp->member_id = $model->member_id;
                        $modelResp->credential_id = $model->credential_id;
                        $modelResp->complete_dt = $model->complete_dt;
                        if (!$modelResp->validate() || !$modelResp->save())
                            throw new Exception ('Problem with post.  Errors: ' . print_r($modelResp->errors, true));
                    }
                    return $this->goBack();
                }
                throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
            }
        }
        return $this->renderAjax('create', compact('model', 'modelResp'));

    }

	public function actionSummaryJson($id)
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

        if (!Yii::$app->user->can('browseTraining')) {
            echo Json::encode($this->renderAjax('/partials/_deniedview'));
        } else {

            $member = Member::findOne($id);

            echo Json::encode($this->renderAjax('_credentials', [
                'member' => $member,
                'recurProvider' => getProvider($id, CredCategory::CATG_RECURRING),
                'nonrecurProvider' => getProvider($id, CredCategory::CATG_NONRECUR),
                'medtestsProvider' => getProvider($id, CredCategory::CATG_MEDTESTS),
                'coreProvider' => getProvider($id, CredCategory::CATG_CORE),
            ]));
        }
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
                                    $this->alertCell($sheet, 'H7:J9');
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

            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            header("Content-Type: application/force-download");
            header("Content-Type: application/{$extension}; charset=utf-8");
            header("Content-Disposition: attachment; filename={$file_nm}.{$extension}");
            header("Expires: 0");

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');

            return $this->goBack();
        }

        throw new ForbiddenHttpException("You are not allowed to perform this action ({$member_id})");
    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        if (Yii::$app->user->can('manageTraining')) {
            $command = Yii::$app->db->createCommand("DELETE FROM MemberCredentials WHERE `id` = :id;", [':id' => $id]);
            try {
                $command->execute();
                Yii::$app->session->setFlash('success', "Credential entry deleted. Previous entry promoted");
            } catch  (Exception $e) {
                Yii::$app->session->setFlash('error', "Problem with post.  See log for details. Code `MCC110`");
                Yii:error("*** MCC110 Delete MemberCredential {$id} failed. Errors: " . print_r($e->errorInfo, true));
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