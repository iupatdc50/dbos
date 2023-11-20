<?php

namespace app\controllers;

use app\models\member\ClassCode;
use app\models\member\Member;
use app\models\training\ArchiveTimesheetForm;
use app\models\training\Timesheet;
use app\models\training\WorkHour;

use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * TimesheetController implements the CRUD actions for Timesheet model.
 */
class TimesheetController extends Controller
{

    /** @var $member Member */
    public $member;


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Timesheet models for a member.
     * @param $member_id
     * @return mixed
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionIndex($member_id)
    {
        Yii::$app->user->returnUrl = Yii::$app->request->url;

        $member = $this->getMember($member_id);

        $count = Yii::$app->db->createCommand(
            "SELECT COUNT(*) FROM Timesheets WHERE member_id = :member_id;",
            [':member_id' => $member_id])->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql' => Timesheet::getFlattenedTimesheetsSql($member->processes),
            'params' => ['member_id' => $member_id],
            'pagination' => ['pageSize' => 15],
            'totalCount' => $count,
            'key' => 'id',
        ]);

        $summary = $member->workHoursSummary;
        $totals = [];

        foreach ($summary as $wp)
            $totals[$wp->wp_seq] = $wp->hours;

        $grand_tot = array_sum($totals);
        $totals['grand_tot'] = $grand_tot;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'member' => $member,
            'totals' => $totals,
        ]);
    }

    public function actionAuditAjax()
    {
        $model = Timesheet::findOne($_POST['expandRowKey']);
        return $this->renderAjax('_audit', ['model' => $model]);
    }

    /**
     * @param $member_id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionCreate($member_id)
    {
        $modelTimesheet = new Timesheet();
        $member = $this->getMember($member_id);

        $cols = [];
        foreach ($member->processes as $process)
            $cols[] = $process->id;
        $modelHours = new DynamicModel($cols);
        $modelHours->addRule($cols, 'number');

        if ($modelTimesheet->load(Yii::$app->request->post())) {
            $modelTimesheet->member_id = $member_id;
            $image = $modelTimesheet->uploadImage();
            if ($modelTimesheet->save()) {
                if ($image !== false) {
                    $path = $modelTimesheet->getImagePath();
                    if ($image->saveAs($path) === false) {
                        Yii::$app->session->addFlash('error', 'Could not upload DPR image. Check file size. Code `TC005`');
                        Yii::error("*** TC005 Upload failed.  Messages:  (`{$member->member_id}`).  Messages: " . print_r($image->error, true));
                    }

                }

                if ($modelHours->load(Yii::$app->request->post())) {
                    foreach ($cols as $col) {
                        if (isset($modelHours->$col) && strlen($modelHours->$col > 0)) {
                            $modelWork = new WorkHour([
                                'timesheet_id' => $modelTimesheet->id,
                                'lob_cd' => $member->currentStatus->lob_cd,
                                'wp_seq' => $member->processes[$col]->seq,
                                'hours' => $modelHours->$col,
                            ]);
                            $modelWork->save();
                        }
                    }
                }

                Yii::$app->session->addFlash('success', "Timesheet successfully added");
            } else {
                Yii::$app->session->addFlash('error', 'Problem saving timesheet. Check log for details. Code `TC010`');
                Yii::error("*** TC010  Timesheet save error (`{$member->member_id}`).  Messages: " . print_r($modelTimesheet->errors, true));
            }

            return $this->goBack();
        }

        if (!isset($modelTimesheet->license_nbr))
            $modelTimesheet->license_nbr = $member->employer->dues_payor;

        return $this->renderAjax('create', [
            'modelTimesheet' => $modelTimesheet,
            'modelHours' => $modelHours,
            'processes' => $member->processes,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $modelTimesheet = $this->findModel($id);
        $query = WorkHour::find()->where(['timesheet_id' => $id])->orderBy('wp_seq');
        $hoursProvider = new ActiveDataProvider(['query' => $query]);

        if ($modelTimesheet->load(Yii::$app->request->post()) && $modelTimesheet->save()) {
            Yii::$app->session->addFlash('success', "Timesheet successfully updated");
            $this->goBack();
        }

        return $this->renderAjax('update', [
            'modelTimesheet' => $modelTimesheet,
            'hoursProvider' => $hoursProvider,
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionAttach($id)
    {
        $model = $this->findModel($id);
        $oldPath = $model->getImagePath();
        $oldId = $model->doc_id;

        if ($model->load(Yii::$app->request->post())) {
            $image = $model->uploadImage();

            if($image === false)
                $model->doc_id = $oldId;

            if	($model->save()) {
                if ($image !== false) {
                    if (file_exists($oldPath))
                        unlink($oldPath);
                    $path = $model->getImagePath();
                    /* @var $image UploadedFile */

                    if ($image->saveAs($path))
                        Yii::$app->session->addFlash('success', "DPR form successfully uploaded for Acct Month `$model->acct_month`");
                    else {
                        Yii::$app->session->addFlash('error', "Could not upload DPR image. Check file size. Code `TC020`");
                        Yii::error("*** TC020 Upload failed.  Messages: " . print_r($image->error, true));
                    }
 
                }

                return $this->redirect(['index', 'member_id' => $model->member_id]);
            }
        }
        return $this->renderAjax('attach', ['model' => $model]);

    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionSummaryJson($id)
    {
        $member = $this->getMember($id);
        $class = isset($member->currentClass) ? $member->currentClass->member_class : null;
        $hoursProvider = new ActiveDataProvider([
            'query' => $this->getMember($id)->getWorkHoursSummary(),
        ]);
        return $this->asJson($this->renderAjax('_summary', [
            'hoursProvider' => $hoursProvider,
            'id' => $id,
            'class' => $class,
        ]));

    }

    /**
     * Deletes an existing Timesheet model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->goBack();
    }

    /**
     * @param $member_id
     * @return array|string
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionArchive($member_id)
    {
        $member = $this->getMember($member_id);
        $archiveForm = new ArchiveTimesheetForm();

        if (Yii::$app->request->isAjax && $archiveForm->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($archiveForm);
        }

        if ($archiveForm->load(Yii::$app->request->post())) {

            $is_mh = ($archiveForm->is_mh == 1) ? "T" : "F";
            if (Timesheet::archiveByTrade($archiveForm->member_id, $archiveForm->lob_cd, $is_mh) > 0) {
                $msg = "Successfully archived DPR timesheets for trade `{$archiveForm->lob_cd}`";
                Yii::$app->session->addFlash('success', $msg);
            } else {
                $msg = "Nothing to archive for trade `{$archiveForm->lob_cd}`";
                Yii::$app->session->addFlash('notice', $msg);
            }

            return $this->goBack();
        }

        $archiveForm->member_id = $member_id;
        $archiveForm->member_nm = $member->fullName;
        if (isset($member->currentStatus)) {
            $archiveForm->lob_cd = $member->currentStatus->lob_cd;
            $archiveForm->lob_descrip = $member->currentStatus->lob->short_descrip;
        }
        $archiveForm->is_mh = (isset($member->currentClass) && ($member->currentClass->member_class) == ClassCode::CLASS_HANDLER) ? 1 : 0;

        return $this->renderAjax('archive', [
            'archiveForm' => $archiveForm,
        ]);
    }

    /**
     * @param $member_id
     * @return array|string
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionRestore($member_id)
    {
        $member = $this->getMember($member_id);
        $archiveForm = new ArchiveTimesheetForm();

        if (Yii::$app->request->isAjax && $archiveForm->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($archiveForm);
        }

        if ($archiveForm->load(Yii::$app->request->post())) {

            if (Timesheet::restoreByTrade($archiveForm->member_id, $archiveForm->lob_cd) > 0) {
                $msg = "Successfully restored DPR timesheets for trade `{$archiveForm->lob_cd}`";
                Yii::$app->session->addFlash('success', $msg);
            } else {
                $msg = "Nothing to restore for trade `{$archiveForm->lob_cd}`";
                Yii::$app->session->addFlash('notice', $msg);
            }

            return $this->goBack();
        }

        $archiveForm->member_id = $member_id;
        $archiveForm->member_nm = $member->fullName;
        if (isset($member->currentStatus)) {
            $archiveForm->lob_cd = $member->currentStatus->lob_cd;
            $archiveForm->lob_descrip = $member->currentStatus->lob->short_descrip;
        }

        return $this->renderAjax('restore', [
            'archiveForm' => $archiveForm,
        ]);
    }

    /**
     * Allows for injection of $this->member
     * @param string $id
     * @throws NotFoundHttpException
     * @return Member
     */
    public function getMember($id)
    {
        if (!isset($this->member))
            if (($this->member = Member::findOne($id)) == null)
                throw new NotFoundHttpException('The requested page does not exist.');
        return $this->member;
    }

    /**
     * Finds the Timesheet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Timesheet the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Timesheet::findOne($id)) !== null)
            return $model;
        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
