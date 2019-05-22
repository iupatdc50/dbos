<?php

namespace app\controllers;

use app\models\member\Member;
use app\models\training\Timesheet;
use app\models\training\WorkProcess;
use yii\data\SqlDataProvider;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TimesheetController implements the CRUD actions for Timesheet model.
 */
class TimesheetController extends Controller
{
    /**
     * @var Member
     */
    private $_member;
    /**
     * @var array
     */
    private $_processes;

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
     */
    public function actionIndex($member_id)
    {
        $this->setupProcesses($member_id);

        $dataProvider = new SqlDataProvider([
            'sql' => Timesheet::getFlattenedTimesheetsSql($this->_processes),
            'params' => ['member_id' => $member_id],
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'member' => $this->_member,
            'processes' => $this->_processes,
        ]);
    }

    public function actionCreate($member_id)
    {
        $this->setupProcesses($member_id);
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

        return $this->redirect(['index']);
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

    protected function setupProcesses($member_id)
    {
        $this->_member = Member::findOne($member_id);
        $this->_processes = WorkProcess::find()->where([
            'lob_cd' => $this->_member->currentStatus->lob_cd,
        ])->orderBy('seq')->all();

    }
}
