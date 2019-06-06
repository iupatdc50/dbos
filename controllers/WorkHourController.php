<?php


namespace app\controllers;

use app\models\training\Timesheet;
use app\models\training\WorkHour;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;


class WorkHourController extends Controller
{

    public function actionCreate($timesheet_id)
    {
        $modelTimesheet = Timesheet::findOne($timesheet_id);
        $modelHour = new WorkHour([
            'timesheet_id' => $timesheet_id,
            'lob_cd' => $modelTimesheet->member->currentStatus->lob_cd,
        ]);

        if ($modelHour->load(Yii::$app->request->post())) {

            if ($modelHour->save()) {
                Yii::$app->session->addFlash('success', "Successfully added `{$modelHour->workProcess->work_process}` hours.");
                return $this->goBack();
            }

            Yii::$app->session->addFlash('error', 'Problem saving work hours. Check log for details. Code `WHC010`');
            Yii::error("*** TC010  WoerkHour save error (`{$timesheet_id}`).  Messages: " . print_r($modelHour->errors, true));

        }

        return $this->renderAjax('create', [
            'modelHour' => $modelHour,
            'modelTimesheet' => $modelTimesheet,
        ]);

    }

    /**
     * @throws Exception
     * @throws Yii\web\NotFoundHttpException
     * @throws ReflectionException
     */
    public function actionEdit()
    {
        if (Yii::$app->request->post('hasEditable')) {
            $id = Yii::$app->request->post('editableKey');
            $model = $this->findModel($id);
            $class = (new ReflectionClass(get_class($model)))->getShortName();

            // $posted is the posted data for StagedAllocation without any indexes
            $posted = current($_POST[$class]);
            // $post is the converted array for single model validation
            $post = [$class => $posted];
            $message = '';

            if ($model->load($post)) {

                if ($model->save()) {

                    $output = isset($posted['wp_seq']) ? $model->workProcess->work_process : Yii::$app->formatter->asDecimal($model->hours, 2);
                    $out = Json::encode(['output' => $output, 'message' => $message]);
                    echo $out;
                    return;
                }
            }
            throw new Exception ('Problem with post. Errors: ' . print_r($model->errors, true));
        }
    }

    /**
     * @param $id
     * @return Response
     * @throws Yii\web\NotFoundHttpException
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
//        $timesheet_id = $model->timesheet_id;

        $model->delete();

        return $this->goBack();

    }

    /**
     * @param $id
     * @return WorkHour|array|ActiveRecord|null
     * @throws Yii\web\NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = WorkHour::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new yii\web\NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }
}