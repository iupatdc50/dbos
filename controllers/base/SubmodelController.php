<?php

namespace app\controllers\base;

use Yii;
use app\models\base\BaseEndable;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\StringHelper;

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
    
    private $_basename;

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
	 * @todo Add relational attr as a hidden field on each concrete form so that it can be set and validated client side
     * @return mixed
     */
    public function actionCreate($relation_id)
    {
    	/** @var ActiveRecord $model */
    	$model = new $this->recordClass;
        
        if ($model->load(Yii::$app->request->post())) {
			// Change this vvv
        	$model->{$this->relationAttribute} = $relation_id;
        	if ($model->save()) {
				Yii::$app->session->addFlash('success', "{$this->getBasename()} entry created");
        		return $this->goBack();
        	}
        	throw new \Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
        } 
        $this->initCreate($model);
//        $model->{$this->relationAttribute} = $relation_id;
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
        	Yii::$app->session->addFlash('success', "{$this->getBasename()} entry updated");
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
        $model = $this->findModel($id);
        
        $removing_current = false;
        if (($model instanceof BaseEndable) && ($model->end_dt == null)) {
        	$removing_current = true;
        	$qualifier = $model->qualifier();
        	$relation_id = $model->$qualifier;
        }
        
        $model->delete();
        
        if ($removing_current)
        	call_user_func([$this->recordClass, 'openLatest'], $relation_id);
        
        Yii::$app->session->addFlash('success', "{$this->getBasename()} entry deleted");
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
    
    protected function initCreate($model)
    {
    	
    }
    
    protected function getBasename()
    {
    	if (!isset($this->_basename))
    		$this->_basename = StringHelper::basename($this->recordClass);
    	return $this->_basename;
    }
    
}
