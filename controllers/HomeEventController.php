<?php

namespace app\controllers;

use Yii;
use app\models\HomeEvent;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HomeEventController implements the CRUD actions for HomeEvent model.
 */
class HomeEventController extends Controller
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
     * Displays a single HomeEvent model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new HomeEvent model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new HomeEvent();

        if ($model->load(Yii::$app->request->post())) {
        	if ($model->validate()) {
        		if ($model->save()) {
        			Yii::$app->session->addFlash('success', "Calendar event created");
        			return $this->redirect(['/']);
        		}
	        	throw new \Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
        	}
            
        } 
        return $this->renderAjax('create', compact('model'));
    }

    /**
     * Deletes an existing HomeEvent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->addFlash('success', "Calendar event deleted");
        return $this->redirect(['/']);
    }

    /**
     * Finds the HomeEvent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return HomeEvent the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HomeEvent::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
