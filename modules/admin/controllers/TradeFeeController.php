<?php

namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\models\TradeFee;
use app\modules\admin\models\TradeFeeSearch;
use app\modules\admin\models\FeeType;
use app\models\value\Lob;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * TradeFeeController implements the CRUD actions for TradeFee model.
 */
class TradeFeeController extends Controller
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
     * Lists all TradeFee models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TradeFeeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $lobPicklist = ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'lob_cd');
        $feetypePicklist = ArrayHelper::map(FeeType::find()->orderBy('fee_type')->all(), 'fee_type', 'descrip');
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'lobPicklist' => $lobPicklist,
        	'feetypePicklist' => $feetypePicklist,
        ]);
    }

    /**
     * Displays a single TradeFee model.
     * @param string $lob_cd
     * @param string $fee_type
     * @return mixed
     */
    public function actionView($lob_cd, $fee_type)
    {
        return $this->render('view', [
            'model' => $this->findModel($lob_cd, $fee_type),
        ]);
    }

    /**
     * Creates a new TradeFee model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TradeFee();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'lob_cd' => $model->lob_cd, 'fee_type' => $model->fee_type]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TradeFee model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $lob_cd
     * @param string $fee_type
     * @return mixed
     */
    public function actionUpdate($lob_cd, $fee_type)
    {
        $model = $this->findModel($lob_cd, $fee_type);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'lob_cd' => $model->lob_cd, 'fee_type' => $model->fee_type]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TradeFee model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $lob_cd
     * @param string $fee_type
     * @return mixed
     */
    public function actionDelete($lob_cd, $fee_type)
    {
        $this->findModel($lob_cd, $fee_type)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TradeFee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $lob_cd
     * @param string $fee_type
     * @return TradeFee the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($lob_cd, $fee_type)
    {
        if (($model = TradeFee::findOne(['lob_cd' => $lob_cd, 'fee_type' => $fee_type])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
