<?php

namespace app\controllers;

use Yii;
use app\models\user\User;
use app\models\user\UserSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\yii\web;
use yii\data\yii\data;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
        				'only' => ['index', 'create', 'view', 'delete'],
        				'rules' => [
        						[
        								'allow' => true,
        								'actions' => ['index', 'view', 'default-pw'],
        								'roles' => ['assignRole'],
        						],
        						[
        								'allow' => true,
        								'actions' => ['create', 'reset-pw'],
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
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
    	$model = $this->findModel($id);
    	if (Yii::$app->user->can('assignRole', ['user' => $model])) {
    		$rolesModel = new ActiveDataProvider([
    				'query' => $model->getAssignments(),
    		]);
        	return $this->render('view', [
        			'model' => $model,
        			'rolesModel' => $rolesModel,
         	]);
    	}
    	throw new ForbiddenHttpException("You are not allowed to view this record ({$model->id})");
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User(['scenario' => User::SCENARIO_CREATE]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) 
            return $this->redirect(['view', 'id' => $model->id]);
        $model->password_clear = User::RESET_USER_PW;
        $model->role = 10;
        if (!isset($model->status))
         	$model->status = User::STATUS_ACTIVE;
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

    	if (Yii::$app->user->can('updateUser', ['user' => $model])) {
	        if ($model->load(Yii::$app->request->post()) && $model->save()) 
	            return $this->redirect(['view', 'id' => $model->id]);
	        return $this->render('update', ['model' => $model]);
        }
    	throw new ForbiddenHttpException("You are not allowed to perform this action ({$model->id})");
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionResetPw()
    {
		$this->layout = 'login';
    	$model = Yii::$app->user->identity;

	    $model->scenario = User::SCENARIO_CHANGE_PW;
	    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
	    	$model->setPassword($model->password_new);
	    	$model->save(false);
	    	Yii::$app->session->addFlash('success', "Successfully changed password.  Please logout and back in.");
	    	return $this->goBack();
	    }
	    return $this->render('change-pw', ['model' => $model]);
    }
    
    public function actionDefaultPw($id)
    {
    	$model = $this->findModel($id);
    	$model->setPassword(User::RESET_USER_PW);
    	$model->save(false);
    	Yii::$app->session->addFlash('success', "Successfully set password to system default.");
    	return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
