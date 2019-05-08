<?php

namespace app\controllers;

use app\models\training\CredCategory;
use app\models\training\MemberCredential;
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
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
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

        if ($model->load(Yii::$app->request->post())) {
            // Because member ID is unchanged, it gets cleared on load
            if ($model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->addFlash('success', "Credential entry created");
                    return $this->goBack();
                }
                throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
            }
        }
        return $this->renderAjax('create', compact('model'));

    }

	public function actionSummaryJson($id)
	{

	    function getProvider(Member $member, $catg)
        {
            $query = $member->getCredentials($catg);
            $provider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['display_seq' => SORT_ASC]],
                'pagination' => false,
            ]);
            return $provider;
        }

        if (!Yii::$app->user->can('manageTraining')) {
            echo Json::encode($this->renderAjax('/partials/_deniedview'));
        } else {

            $member = Member::findOne($id);

            echo Json::encode($this->renderAjax('_credentials', [
                'member' => $member,
                'recurProvider' => getProvider($member, CredCategory::CATG_RECURRING),
                'nonrecurProvider' => getProvider($member, CredCategory::CATG_NONRECUR),
                'medtestsProvider' => getProvider($member, CredCategory::CATG_MEDTESTS),
                'coreProvider' => getProvider($member, CredCategory::CATG_CORE),
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
     */
	public function actionCertificate($member_id)
    {
        $member = Member::findOne($member_id);
        $credentials = $member->getCredentials()->where(['show_on_cert' => 'T'])->orderBy(['display_seq' => SORT_ASC])->all();

        $template_nm = 'TRAINING CERTIFICATION.xltx';
        $file_nm = 'Cert_' . substr($member->report_id, -4);
        $extension = 'xlsx';
        $template_path = implode(DIRECTORY_SEPARATOR, [Yii::$app->getRuntimePath(), 'templates', 'xlsx', $template_nm]);

        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($template_path);

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getCell($objPHPExcel->getNamedRange('full_nm')->getRange())->setValue($member->fullName);
        $sheet->getCell($objPHPExcel->getNamedRange('trade')->getRange())->setValue($member->currentStatus->lob->short_descrip);

        foreach ($credentials as $credential)
        {
            /* @var $credential MemberCredential */
            $complete_dt = PHPExcel_Shared_Date::PHPToExcel(strtotime($credential->complete_dt));
            $range = $objPHPExcel->getNamedRange('complete_dt' . $credential->credential_id)->getRange();
            $sheet->getCell($range)->setValue($complete_dt);
            $named_range = $objPHPExcel->getNamedRange('expire_dt' . $credential->credential_id);
            if (isset($named_range) && (($range = $named_range->getRange()) != null)) {
                $expire_timestamp = strtotime($credential->expire_dt);
                $expire_dt = ($expire_timestamp > time()) ? PHPExcel_Shared_Date::PHPToExcel(strtotime($credential->expire_dt)) : 'Expired';
                $sheet->getCell($range)->setValue($expire_dt);
                if ($expire_dt == 'Expired') {
                    $this->alertCell($sheet, $range);
                    // Haz/lead includes other credentials in certificate
                    if ($credential->credential_id == 25)
                        $this->alertCell($sheet, 'H8:J10');
                }
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

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        if (($model = MemberCredential::findOne($id)) == null)
            throw new NotFoundHttpException('The requested page does not exist');
        $model->delete();
        Yii::$app->session->setFlash('success', "Credential entry deleted. Previous entry promoted");
        return $this->goBack();
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