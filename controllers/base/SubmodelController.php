<?php

namespace app\controllers\base;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SubmodelController implements the CRUD actions for a primary model's submodel.
 * 
 * Assumes besides the foreign key reference in the model that its own primary
 * key is a surrogate called `id`
 */
class SubmodelController extends Controller
{
	/** @var string Name of the class to be manipulated */
	public $recordClass;
	
    /** @var string Name of the attribute which will store the given relation ID */
    public $relationAttribute;

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
     * Creates a new ActiveRecord model.
     * 
     * Assumes that all submodel creates are ajax 
	 *
     * @return mixed
     */
    public function actionCreate($relation_id)
    {
    	/** @var ActiveRecord $model */
    	$model = new $this->recordClass;
        
        if ($model->load(Yii::$app->request->post())) {
        	// Prepopulate referencing column
        	$model->{$this->relationAttribute} = $relation_id;
        	if ($model->save()) {
        		return $this->goBack();
        	}
        	throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
        } 
        return $this->renderAjax('create', compact('model'));
        
    }

    /**
     * Updates an existing ActiveRecord model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->goBack();
        } 
        return $this->render('update', compact('model'));
    }

    /**
     * Deletes an existing ActiveRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->goBack();
    }

    /**
     * Finds the ActiveRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return \yii\db\ActiveRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = call_user_func([$this->recordClass, 'findOne'], $id);
        if (!$model) {
        	throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }
    
}
