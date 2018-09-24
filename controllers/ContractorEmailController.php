<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\contractor\Contractor;
use app\models\contractor\Email;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * ContractorEmailController implements the CRUD actions for ContractorEmail model.
 */
class ContractorEmailController extends Controller
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
     * @param $relation_id
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionCreate($relation_id)
    {
        if (($contractor = Contractor::findOne($relation_id)) == null)
            throw new \InvalidArgumentException('Invalid license_nbr passed: ' . $relation_id);
        $model = new Email([
            'scenario' => Email::SCENARIO_CONTRACTOREXISTS,
            'contractor' =>$contractor,
        ]);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Email entry created");
                return $this->goBack();
            }
            throw new \Exception ('Problem with post.  Errors: ' . print_r($model->errors, true));
        }
        return $this->renderAjax('create', compact('model'));

    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', "Email entry updated");
            return $this->goBack();
        }
        return $this->render('update', compact('model'));
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('success', "Email entry deleted");
        return $this->goBack();
    }

    /**
     * @param $id
     * @return Email
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Email::findOne($id)) == null)
            throw new NotFoundHttpException('The requested page does not exist');
        return $model;
    }

}
