<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\Status;
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
		$member = Member::findOne($id);
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
	
}
