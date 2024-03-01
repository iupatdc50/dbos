<?php

namespace app\controllers\base;

use Exception;
use Throwable;
use Yii;
use app\models\base\BaseEndable;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\StringHelper;
use yii\web\Response;

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
                'class' => VerbFilter::class,
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
     * @return array|string|Response
     * @throws Exception
     * @todo Add relational attr as a hidden field on each concrete form so that it can be set and validated client side
     */
    public function actionCreate($relation_id)
    {
    	/** @var ActiveRecord $model */
    	$model = new $this->recordClass;
        
        if ($model->load(Yii::$app->request->post())) {
			// Change this vvv
        	$model->{$this->relationAttribute} = $relation_id;
        	if ($model->validate()) {
	        	if ($model->save()) {
					Yii::$app->session->addFlash('success', "{$this->getBasename()} entry created");
	        		return $this->goBack();
	        	}
	        	throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
        	}
        } 
        $this->initCreate($model);
//        $model->{$this->relationAttribute} = $relation_id;
        return $this->renderAjax('create', compact('model'));
        
    }

    /**
     * Updates an existing ActiveRecord model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return Response|string
     * @throws NotFoundHttpException
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
     * @throws NotFoundHttpException|Throwable
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $removing_current = false;
        $relation_id = null;
        if ($model instanceof BaseEndable) {

            if (!$this->canDelete($model)) {
                Yii::$app->session->addFlash('error', "By rule, {$this->getBasename()} entry cannot be deleted.");
                return $this->goBack();
            }


            if ($model->end_dt == null) {
                $removing_current = true;
                $qualifier = $model->qualifier();
                $relation_id = $model->$qualifier;
            }

        }

        try {
            if ($model->delete())
                Yii::$app->session->addFlash('success', "{$this->getBasename()} entry deleted");
        } catch (StaleObjectException $e) {
        } catch (Exception $e) {
            Yii::$app->session->addFlash('error', 'Problem deleting model. Check log for details. Code `SC050`');
            Yii::error("*** SC050  Model delete error (ID: `$id`).  Messages: " . print_r($model->errors, true));
        }

        if ($removing_current)
        	call_user_func([$this->recordClass, 'openLatest'], $relation_id);
        
        return $this->goBack();
    }

    /**
     * Finds the ActiveRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActiveRecord the loaded model
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

    /**
     * @param BaseEndable $model
     * @return true
     */
    protected function canDelete(BaseEndable $model)
    {
        return true;
    }
    
    protected function getBasename()
    {
    	if (!isset($this->_basename))
    		$this->_basename = StringHelper::basename($this->recordClass);
    	return $this->_basename;
    }
    
}
