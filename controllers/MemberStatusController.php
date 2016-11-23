<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\Status;
use app\models\member\CcForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\member\Member;

/**
 * MemberStatusController implements the CRUD actions for Status model.
 */
class MemberStatusController extends SummaryController
{

	public $recordClass = 'app\models\member\Status';
	public $relationAttribute = 'member_id';
	
	public function actionSummaryJson($id)
	{
		$member = $this->findMember($id);
		$status = isset($member->currentStatus) ? $member->currentStatus->member_status : Status::INACTIVE;
		$this->viewParams = ['status' => $status];
		parent::actionSummaryJson($id);
	}

	public function actionReinstate($id) {
	
		// Special receipt with dues, reinstatement fee & revised application dt (optional) amounts on it
	
	}
	
	public function actionSuspend($id) {
	
		// Special receipt with dues, reinstatement fee & revised application dt (optional) amounts on it
	
	}
	
	public function actionDrop($id) {
	
		// Special receipt with dues, reinstatement fee & revised application dt (optional) amounts on it
	
	}
	
	public function actionGrantCc($member_id) 
	{	
		/** @var Model $model */
		$model = new CcForm();
		
		if ($model->load(Yii::$app->request->post())) {
			$status = new Status([
					'effective_dt' => $model->effective_dt,
					'member_status' => Status::INACTIVE,
					'reason' => Status::REASON_CCG . $model->other_local,
			]);
			$member = $this->findMember($member_id);
			if ($member->addStatus($status)) {
				return $this->goBack();
			}
			throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		return $this->renderAjax('cc-form', compact('model'));
		
	}
	
	public function findMember($id)
	{
		if (($model = Member::findOne($id)) == null)
			throw new NotFoundHttpException('The requested page does not exist.');
		return $model;
	}
	
	
	
}
