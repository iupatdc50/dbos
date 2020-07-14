<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use app\helpers\OptionHelper;
use app\models\member\ClassCode;
use app\models\training\Timesheet;
use app\models\value\Lob;
use Yii;
use app\models\member\MemberClass;
use app\models\member\Member;
use yii\db\Exception;
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
     * @param string $relation_id      Member ID
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws Exception
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

    	    $was_handler = false;
    	    if (strncmp($model->class_id, ClassCode::CLASS_APPRENTICE, 1) == 0)
                if (isset($this->member->currentClass) && $this->member->currentClass->member_class == ClassCode::CLASS_HANDLER)
                    $was_handler = true;

            $image = $model->uploadImage();
        	if ($this->addClass($model)) {
        	    if ($image !== false) {
        	        $path = $model->getImagePath();
                    $image->saveAs($path);
                }
            }

        	// Assumes that material handler only applies to floorlayers
        	if ($was_handler)
                Timesheet::archiveByTrade($model->member_id, Lob::TRADE_FL, OptionHelper::TF_TRUE);
        	
        	return $this->goBack();
        } 
        return $this->renderAjax('create', compact('model'));
        
    }

    public function actionSummaryJson($id)
    {
        $class = isset($this->member->currentClass) ? $this->member->currentClass->member_class : null;
        $this->viewParams = [
            'class' => $class,
        ];
        return parent::actionSummaryJson($id);
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
