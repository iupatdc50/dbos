<?php

namespace app\controllers\receipt;

use Yii;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptSearch;
use yii\base\InvalidCallException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\yii\base;


/**
 * BaseController implements the CRUD actions for Receipt model.
 */
class BaseController extends Controller
{
	
	public $layout = 'accounting';
	public $payor_type_filter = null;
			
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
     * Lists all Receipt models.
     * @return mixed
     */
    public function actionIndex()
    {
    	return $this->redirect("/accounting");
    }

    /**
     * Displays a single Receipt model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing Receipt model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Receipt model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Receipt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Receipt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Receipt::findOne($id)) == null) 
            throw new NotFoundHttpException('The requested page does not exist.');
        return $model;
    }
}
