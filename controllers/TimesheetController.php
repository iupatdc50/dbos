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
        $member = Member::findOne($member_id);
        $processes = WorkProcess::find()->where([
            'lob_cd' => $member->currentStatus->lob_cd,
        ])->orderBy('seq')->all();

        $dataProvider = new SqlDataProvider([
            'sql' => Timesheet::getFlattenedTimesheetsSql($processes),
            'params' => ['member_id' => $member_id],
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'member' => $member,
            'processes' => $processes,
        ]);
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
        if (($model = Timesheet::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
