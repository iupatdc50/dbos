<?php

namespace app\modules\admin\controllers;

use app\helpers\ExceptionHelper;
use app\helpers\TokenHelper;
use app\models\accounting\DuesStripeProduct;
use app\models\accounting\FeeCalendar;
use app\models\accounting\StripeProductManager;
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
     * @return string
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

    public function actionStripeProdJson()
    {
        if (isset($_POST['expandRowKey'])) {

            $key = $_POST['expandRowKey'];

            $product = DuesStripeProduct::findOne($key);

            return $this->asJson($this->renderAjax('_stripeprod', [
                'id' => $key,
                'product' => $product,
            ]));
        }
        Yii::$app->session->addFlash('error', 'No Dues Rate row selected [Error: DRC010]');
        return $this->goBack();

    }

    public function actionCreateProduct($id)
    {
        $rate = DuesRate::findOne($id);
        $manager = new StripeProductManager(['rate' => $rate]);

        if (($price = $manager->createProduct()) == false) {
            foreach ($manager->messages as $code => $message)
                $this->exceptionHandler($code, $message['friendly'], $message['system']);
            return $this->goBack();
        }

        $product = new DuesStripeProduct([
            'dues_rate_id' => $rate->id,
            'stripe_id' => $price->product,
            'stripe_price_id' => $price->id,
        ]);

        if ($product->save())
            Yii::$app->session->addFlash('success', "Stripe product `$product->stripe_id` added for trade `$rate->lob_cd`" );
        else
            $this->exceptionHandler('DRC015', 'Internal Error', [$product->errors]);

        return $this->goBack();

    }

    public function actionDeleteProduct($id)
    {
        Yii::$app->session->addFlash('notice', "Stripe product deletion is currently not available in this interface.  [Attempted on `$id`]" );
        return $this->goBack();
    }

    /**
     * Creates a new DuesRate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
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
     * @return Response
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $removing_current = false;
        $ids = [];
        if ($model->end_dt == null) {
            $removing_current = true;
            $ids = ['lob_cd' => $model->lob_cd, 'rate_class' => $model->rate_class];
        }

        $model->delete();

        if ($removing_current)
            DuesRate::openLatest($ids);

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

    protected function exceptionHandler($code, $message, array $errors)
    {
        ExceptionHelper::handleError(Yii::$app->session, $code, $message, $errors);
    }


}
