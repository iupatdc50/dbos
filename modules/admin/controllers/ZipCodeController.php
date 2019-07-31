<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\ZipCode;
use app\models\ZipCodeSearch;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ZipCodeController implements the CRUD actions for ZipCode model.
 */
class ZipCodeController extends Controller
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
     * Lists all ZipCode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ZipCodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ZipCode model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ZipCode model.
     *
     * @param null $zip_cd
     * @return mixed
     */
    public function actionCreate($zip_cd = NULL)
    {
        $model = new ZipCode();

        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->goBack();
            
        if (isset($zip_cd) && !isset($model->zip_cd))
        	$model->zip_cd = $zip_cd;
        
        if (Yii::$app->request->isAjax)
        	return $this->renderAjax('create', ['model' => $model]);
        
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing ZipCode model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->zip_cd]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionGetCityLn($zip_cd)
    {
        $cityLn = (($model = ZipCode::findOne($zip_cd)) !== null) ? $model->getCityLn(false) : '';
        return $this->asJson($cityLn);
    }

    /**
     * Deletes an existing ZipCode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
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
     * Finds the ZipCode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ZipCode the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ZipCode::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
