<?php

namespace app\modules\admin\controllers;

use app\helpers\TokenHelper;
use app\models\accounting\FeeCalendar;
use Throwable;
use Yii;
use app\models\accounting\DuesRate;
use app\models\accounting\DuesRateSearch;
use app\models\value\Lob;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * DuesRateController implements the CRUD actions for DuesRate model.
 */
class DuesRateController extends Controller
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
     * Lists all DuesRate models.
     * @return mixed
     */
    public function actionIndex()
    {
        Yii::$app->user->returnUrl = Yii::$app->request->url;

        $searchModel = new DuesRateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $lobPicklist = ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'lob_cd');
        $token = TokenHelper::getData(FeeCalendar::TOKEN_REFRESH);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'lobPicklist' => $lobPicklist,
            'token' => $token,
        ]);
    }

    /**
     * Creates a new DuesRate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DuesRate();

        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['index']);
        return $this->renderAjax('create', ['model' => $model]);
    }

    /**
     * Deletes an existing DuesRate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $removing_current = false;
        $id = [];
        if ($model->end_dt == null) {
            $removing_current = true;
            $id = ['lob_cd' => $model->lob_cd, 'rate_class' => $model->rate_class];
        }

        $model->delete();

        if ($removing_current)
            DuesRate::openLatest($id);

        return $this->redirect(['index']);
    }

    /**
     * Finds the DuesRate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DuesRate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DuesRate::findOne($id)) !== null)
            return $model;
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
