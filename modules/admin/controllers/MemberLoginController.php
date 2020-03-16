<?php

namespace app\modules\admin\controllers;

use Throwable;
use Yii;
use app\models\member\MemberLogin;
use app\models\member\MemberLoginSearch;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User model.
 */
class MemberLoginController extends Controller
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
        		'access' => [
        				'class' => AccessControl::className(),
        				'only' => ['index', 'create', 'view', 'update', 'delete'],
        				'rules' => [
        						[
        								'allow' => true,
        								'actions' => ['index', 'view', 'default-pw'],
        								'roles' => ['assignRole'],
        						],
        						[
        								'allow' => true,
        								'actions' => ['create', 'update', 'reset-pw'],
        								'roles' => ['updateUser'],
        						],
        						[
        								'allow' => true,
        								'actions' => ['delete'],
        								'roles' => ['deleteUser'],
        						],
        				],
        		],
        		
        		
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MemberLoginSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MemberLogin model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
    	$model = $this->findModel($id);
        return $this->render('view', [
                'model' => $model,
        ]);
    }

    /**
     * Creates a new MemberLogin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MemberLogin(['scenario' => MemberLogin::SCENARIO_CREATE]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) 
            return $this->redirect(['view', 'id' => $model->id]);
        $model->password_clear = MemberLogin::RESET_MEMBER_PW;
        if (!isset($model->status))
         	$model->status = MemberLogin::STATUS_ACTIVE;
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing MemberLogin model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save())
            return $this->redirect(['view', 'id' => $model->id]);
        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionDefaultPw($id)
    {
    	$model = $this->findModel($id);
    	$model->setPassword(MemberLogin::RESET_MEMBER_PW);
    	$model->save(false);
    	Yii::$app->session->addFlash('success', "Successfully set password to system default.");
    	return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MemberLogin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MemberLogin::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
