<?php

namespace app\controllers\basedoc;

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
 * 
 * This controller is modeled after the one in controllers/base namespace, but includes
 * processing for document attachments
 * 
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
     * If creation is successful, the browser will be redirected to the primary model's 'view' page.
     * @return mixed
     */
    public function actionCreate($relation_id)
    {
    	/** @var ActiveRecord $model */
        $model = new $this->recordClass;
        
        if ($model->load(Yii::$app->request->post())) {
	        // Prepopulate referencing column
	        $model->{$this->relationAttribute} = $relation_id;
        	$image = $model->uploadImage();
        	if	($model->save()) {
        		if ($image !== false) {
        			$path = $model->imagePath;
        			$image->saveAs($path);
        		}
        		return $this->goBack();
        	}
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
        $oldPath = $model->imagePath;
        $oldId = $model->doc_id;
        
        if ($model->load(Yii::$app->request->post())) {
        	$image = $model->uploadImage();
        	
        	if($image === false)
        		$model->doc_id = $oldId;
         
        	if	($model->save()) {
            	if ($image !== false && (($oldPath === null) || unlink($oldPath))) {
    				$path = $model->imagePath;
    				$image->saveAs($path);
    			}
        		return $this->goBack();
        	}
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
        if ($model->delete()) {
        	if (!$model->deleteImage())	
        		Yii::$app->session->setFlash('error', 'Could not delete document');
        }
    	
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
