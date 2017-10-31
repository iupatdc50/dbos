<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\MemberClass;
use app\models\member\Member;
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
	public $member;
	
	/**
	 * Had to override because of the pass by value of $model.  Clean 
	 * this up.
	 * 
	 * @see \app\controllers\base\SubmodelController::actionCreate()
	 */
    public function actionCreate($relation_id)
    {
    	$model = new MemberClass(['scenario' => MemberClass::SCENARIO_CREATE]);
    	$this->setMember($relation_id);
        
    	if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $relation_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
    	if ($model->load(Yii::$app->request->post())) {
        	if ($this->addClass($model)) { }  //stub
        	
        	return $this->goBack();
        } 
        return $this->renderAjax('create', compact('model'));
        
    }

	/**
	 * Allows for injection of $this->member 
	 * @param string $id
	 * @throws NotFoundHttpException
	 * @return \yii\db\static
	 */
	public function setMember($id)
	{
		if (!isset($this->member))
			if (($this->member = Member::findOne($id)) == null)
				throw new NotFoundHttpException('The requested page does not exist.');
		return $this->member;
	}
	
    protected function addClass($model)
    {
    	$result = $this->member->addClass($model);
    	if (strlen($result) > 1)
    		Yii::$app->session->addFlash('error', $result);
    	elseif ($result)
    		Yii::$app->session->addFlash('success', "{$this->getBasename()} entry added for {$model->mClassDescrip}");
    	else {
    		Yii::$app->session->addFlash('error', 'Problem saving Class. Check log for details. Code `MCC010`');
    		Yii::error("*** MCC010  Assessment save error (`{$this->member->member_id}`).  Messages: " . print_r($model->errors, true));
    	}
    	return (strlen($result) > 1) ? false : $result;
    }
    
    
	
}
