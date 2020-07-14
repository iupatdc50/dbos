<?php


namespace app\modules\admin\controllers;


use app\models\value\Lob;
use app\modules\admin\models\Contribution;
use app\modules\admin\models\ContributionSearch;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ContributionController extends Controller
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
     * Lists all Contribution models.
     * @return mixed
     */
    public function actionIndex()
    {
        Yii::$app->user->returnUrl = Yii::$app->request->url;

        $searchModel = new ContributionSearch();
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
        $model = new Contribution();

        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['index']);
        return $this->renderAjax('create', ['model' => $model]);
    }

    /**
     * @param $lob_cd
     * @param $contrib_type
     * @param $wage_pct
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($lob_cd, $contrib_type, $wage_pct)
    {
        $model = $this->findModel($lob_cd, $contrib_type, $wage_pct);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $lob_cd
     * @param $contrib_type
     * @param $wage_pct
     * @return Response
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($lob_cd, $contrib_type, $wage_pct)
    {
        $model = $this->findModel($lob_cd, $contrib_type, $wage_pct);
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * @param $lob_cd
     * @param $contrib_type
     * @param $wage_pct
     * @return Contribution|null
     * @throws NotFoundHttpException
     */
    protected function findModel($lob_cd, $contrib_type, $wage_pct)
    {
        return Contribution::findByKey($lob_cd, $contrib_type, $wage_pct);
    }

}