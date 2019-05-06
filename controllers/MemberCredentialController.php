<?php

namespace app\controllers;

use app\models\training\CredCategory;
use app\models\training\MemberCredential;
use InvalidArgumentException;
use PHPExcel;
use PHPExcel_IOFactory;
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
     * @param $member_id
     * @return string
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     * @throws \PHPExcel_Exception
     */
	public function actionCertificate($member_id)
    {
        $member = Member::findOne($member_id);
        $query = $member->getCredentials()->where(['show_on_cert' => 'T']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['display_seq' => SORT_ASC]],
            'pagination' => false,
        ]);

        $template_nm = 'TRAINING CERTIFICATION.xltx';
        $file_nm = 'Certificate_' . substr($member->report_id, -4) . $member->last_nm;
        $extension = 'xlsx';
        $template_path = implode(DIRECTORY_SEPARATOR, [Yii::$app->getRuntimePath(), 'templates', 'xlsx', $template_nm]);

        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($template_path);

        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
//        $sheet->namedRangeToArray()
        $sheet->getCell('trade')->setValue($member->currentStatus->lob->short_descrip);

        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header("Content-Type: application/force-download");
        header("Content-Type: application/{$extension}; charset=utf-8");
        header("Content-Disposition: attachment; filename={$file_nm}.{$extension}");
        header("Expires: 0");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');

        return $this->render('certificate', ['member' => $member]);
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


}