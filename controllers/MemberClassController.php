<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\MemberClass;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
        
        if ($model->load(Yii::$app->request->post())) {
        	$model->member_id = $relation_id;
        	$model->resolveClasses();
        	if ($model->save()) {
        		return $this->goBack();
        	}
        	throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
        } 
        return $this->renderAjax('create', compact('model'));
        
    }
		
	
}
