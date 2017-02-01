<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\MemberClass;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\bootstrap\ActiveForm;


/**
 * MemberClassController implements the CRUD actions for MemberClass model.
 */
class MemberClassController extends SummaryController
{
	public $recordClass = 'app\models\member\MemberClass';
	public $relationAttribute = 'member_id';

	/**
	 * Had to override because of the pass by value of $model.  Clean 
	 * this up.
	 * 
	 * @see \app\controllers\base\SubmodelController::actionCreate()
	 */
    public function actionCreate($relation_id)
    {
    	$model = new MemberClass(['scenario' => MemberClass::SCENARIO_CREATE]);
        
    	if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $relation_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
    	if ($model->load(Yii::$app->request->post())) {
        	$model->member_id = $relation_id;
        	$model->resolveClasses();
        	if ($model->save()) {
				Yii::$app->session->addFlash('success', "{$this->getBasename()} entry created");
        		return $this->goBack();
        	}
			Yii::$app->session->addFlash('error', 'Problem adding Member Class. Check log for details. Code `MCC010`');
			Yii::error("*** MSC010  member-class-controller/create(`{$relation_id}`).  Messages: " . print_r($model->errors, true));
        } 
        return $this->renderAjax('create', compact('model'));
        
    }
		
	
}
