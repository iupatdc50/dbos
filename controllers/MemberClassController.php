<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\MemberClass;
use app\models\member\Member;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\bootstrap\ActiveForm;
use yii\web\Response;


/**
 * MemberClassController implements the CRUD actions for MemberClass model.
 */
class MemberClassController extends SummaryController
{
	public $recordClass = 'app\models\member\MemberClass';
	public $relationAttribute = 'member_id';
    /** @var $member Member */
	public $member;

    /**
     * Had to override because of the pass by value of $model.  Clean
     * this up.
     *
     * @param $relation_id      Member ID
     * @return array|string|Response
     * @throws NotFoundHttpException
     *@see \app\controllers\base\SubmodelController::actionCreate()
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
            $image = $model->uploadImage();
        	if ($this->addClass($model)) {
        	    if ($image !== false) {
        	        $path = $model->imagePath;
                    $image->saveAs($path);
                }
            }
        	
        	return $this->goBack();
        } 
        return $this->renderAjax('create', compact('model'));
        
    }

    public function actionSummaryJson($id)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $hoursProvider = new ActiveDataProvider([
            'query' => $this->setMember($id)->getWorkHoursSummary(),
        ]);
        $class = isset($this->member->currentClass) ? $this->member->currentClass->member_class : null;
        $this->viewParams = [
            'class' => $class,
            'hoursProvider' => $hoursProvider,
        ];
        parent::actionSummaryJson($id);
    }

	/**
	 * Allows for injection of $this->member 
	 * @param string $id
	 * @throws NotFoundHttpException
	 * @return Member
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
