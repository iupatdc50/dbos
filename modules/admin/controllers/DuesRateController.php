<?php

namespace app\modules\admin\controllers;

use Throwable;
use Yii;
use app\models\accounting\DuesRate;
use app\models\accounting\DuesRateSearch;
use app\models\value\Lob;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;

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
        $searchModel = new DuesRateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $lobPicklist = ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'lob_cd');
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'lobPicklist' => $lobPicklist,
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
     * Repopulates the calendar table used in the member portal to determine
     * dues balance owed
     *
     * @param int $years Number of years past the last effective date to populate
     *                   calendar
     * @return Response
     * @throws Exception
     */
    public function actionRefreshCalendar($years = 5)
    {
        $db = Yii::$app->db;
        $db->createCommand("CALL PopulateFeeCalendar (:years)", [':years' => $years])->execute();

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
