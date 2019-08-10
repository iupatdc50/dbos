<?php

namespace app\controllers;

use app\models\member\Member;
use app\models\training\Timesheet;
use app\models\training\WorkHour;

use Yii;
use yii\base\DynamicModel;
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
                    $path = $modelTimesheet->imagePath;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $image->saveAs($path);
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

                Yii::$app->session->addFlash('success', "DPR form successfully uploaded");
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
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->goBack();
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
