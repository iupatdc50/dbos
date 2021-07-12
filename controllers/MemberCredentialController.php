<?php

namespace app\controllers;

use app\models\training\CredCategory;
use app\models\training\Credential;
use app\models\training\DrugTestResult;
use app\models\training\MemberCredential;
use app\models\training\MemberCompliance;
use app\models\training\MemberRespirator;
use InvalidArgumentException;
use Yii;
use app\models\member\Member;
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
     * @throws Exception
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

                if ($image != false) {
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
            return new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['display_seq' => SORT_ASC]],
                'pagination' => false,
            ]);
        }

        if (!Yii::$app->user->can('browseTraining'))
            throw new ForbiddenHttpException("You are not allowed to perform this action ($id)");

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
        $show_attach = ($cred->catg != CredCategory::CATG_CORE);

        return $this->renderAjax('_history', [
            'dataProvider' => $dataProvider,
            'credential_id' => $curr->credential_id,
            'show_attach' => $show_attach,
        ]);
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
                    Yii::$app->session->setFlash('error', "Could not delete document `$credential->doc_id`");
            } catch  (Exception $e) {
                Yii::$app->session->setFlash('error', "Problem with post.  See log for details. Code `MCC110`");
                Yii::error("*** MCC110 Delete MemberCredential $id failed. Errors: " . print_r($e->errorInfo, true));
            }
            return $this->goBack();
        }
        throw new ForbiddenHttpException("You are not allowed to perform this action ($id)");

    }

}