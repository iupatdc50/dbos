<?php

namespace app\modules\admin\controllers;

use Throwable;
use Yii;
use app\models\accounting\InitFee;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * InitFeeController implements the CRUD actions for InitFee model.
 */
class InitFeeController extends Controller
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
     * Lists all InitFee models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => InitFee::find()->orderBy([
                'lob_cd' => SORT_ASC,
                'member_class' => SORT_ASC,
                'effective_dt' => SORT_DESC,
            ])
        ]);
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new InitFee model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new InitFee();

        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['index']);
        return $this->renderAjax('create', ['model' => $model]);
    }

    /**
     * Deletes an existing InitFee model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $removing_current = false;
        $id = [];
        if ($model->end_dt == null) {
            $removing_current = true;
            $id = ['lob_cd' => $model->lob_cd, 'member_class' => $model->member_class];
        }

        $model->delete();

        if ($removing_current)
            InitFee::openLatest($id);

        return $this->redirect(['index']);
    }

    /**
     * Finds the InitFee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return InitFee the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = InitFee::findOne($id)) !== null)
            return $model;
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
