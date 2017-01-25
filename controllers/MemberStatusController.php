<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\Status;
use app\models\member\CcForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\bootstrap\ActiveForm;

use app\models\member\Member;
use app\models\accounting\Assessment;
use app\modules\admin\models\FeeType;
use app\models\accounting\AdminFee;
use app\components\utilities\OpDate;

/**
 * MemberStatusController implements the CRUD actions for Status model.
 */
class MemberStatusController extends SummaryController
{

	public $recordClass = 'app\models\member\Status';
	public $relationAttribute = 'member_id';
	public $member;

	public function behaviors()
	{
		return [
				'verbs' => [
						'class' => VerbFilter::className(),
						'actions' => [
								'delete' => ['post'],
						],
				],
				'access' => [
						'class' => AccessControl::className(),
						'only' => ['create', 'drop', 'clear-in'],
						'rules' => [
								[
										'allow' => true,
										'actions' => ['create', 'drop', 'clear-in'],
										'roles' => ['createMember', 'updateMember'],
								],
						],
				],
	
		];
	}
	
	public function actionCreate($relation_id)
	{
		$this->setMember($relation_id);
		return parent::actionCreate($relation_id);
	}
	
	public function actionSummaryJson($id)
	{
		$this->setMember($id);
		$status = isset($this->member->currentStatus) ? $this->member->currentStatus->member_status : Status::INACTIVE;
		$this->viewParams = ['status' => $status];
		parent::actionSummaryJson($id);
	}

	public function actionReset($member_id) 
	{
		if (!Yii::$app->user->can('resetPT'))
			return $this->renderAjax('/partials/_deniedaction');
		
		/** @var Model $model */
		$model = new Status(['scenario' => Status::SCENARIO_RESET]);
		$this->setMember($member_id);
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
			if (!empty($model->reason))
				$model->reason .= '; ';
			$messages = [];
			if (!empty($model->paid_thru_dt)) {
				$this->member->dues_paid_thru_dt = $model->paid_thru_dt;
				$pt_dt = (new OpDate)->setFromMySql($model->paid_thru_dt)->getDisplayDate(false, '/');
				$messages[] = Status::REASON_RESET_PT . $pt_dt;
			}
			if (!empty($model->init_dt)) {
				$this->member->init_dt = $model->init_dt;
				$init_dt = (new OpDate)->setFromMySql($model->init_dt)->getDisplayDate(false, '/');
				$messages[] =  Status::REASON_RESET_INIT . $init_dt;
			}
			$model->reason .= implode('; ', $messages);
			if ($this->member->addStatus($model)) {
				Yii::$app->session->addFlash('success', "{$this->getBasename()} activated");
				
				if ($this->member->save()) {
					Yii::$app->session->addFlash('success', implode('; ', $messages));
					return $this->goBack();
				}
				Yii::$app->session->addFlash('error', 'Problem saving Member. Check log for details. Code `MSC010`'); 
				Yii::error("*** MSC010  member-status-controller/reset(`{$member_id}`).  Messages: " . print_r($this->member->errors, true));
			} else {
				Yii::$app->session->addFlash('error', 'Problem adding Member Status. Check log for details. Code `MSC015`');
				Yii::error("*** MSC015  member-status-controller/reset(`{$member_id}`).  Messages: " . print_r($model->errors, true));
			}
		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
		return $this->renderAjax('create', compact('model'));
	
	}
	
	public function actionDrop($member_id) 
	{
	
		/** @var Model $model */
		$model = new Status();
		$this->setMember($member_id);
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
			if ($this->member->addStatus($model)) {
				Yii::$app->session->addFlash('success', "{$this->getBasename()} entry added for drop");
				$assessModel = new Assessment([
						'member_id' => $member_id,
						'fee_type' => FeeType::TYPE_REINST,
						'assessment_dt' => $model->effective_dt,
						'assessment_amt' => AdminFee::getFee(FeeType::TYPE_REINST, $model->effective_dt),
						'purpose' => 'Dropped on this date',
				]);
				if ($assessModel->save()) {
					Yii::$app->session->addFlash('success', "Reinstate fee of {$assessModel->assessment_amt} assessed");
					return $this->goBack();
				}
				Yii::$app->session->addFlash('error', 'Problem saving assessment. Check log for details. Code `MSC020`'); 
				Yii::error("*** MSC020  member-status-controller/drop(`{$member_id}`).  Messages: " . print_r($assessModel->errors, true));
			} else {
				Yii::$app->session->addFlash('error', 'Problem adding Member Status. Check log for details. Code `MSC025`');
				Yii::error("*** MSC025  member-status-controller/drop(`{$member_id}`).  Messages: " . print_r($model->errors, true));
			}
		}
		$this->initCreate($model);
		$model->member_status = Status::INACTIVE;
		$model->reason = Status::REASON_DROP;
		return $this->renderAjax('create', compact('model'));
		
	}
	
	public function actionClearIn($member_id) 
	{	
		/** @var Model $model */
		$model = new Status(['scenario' => Status::SCENARIO_CCD]);
		$this->setMember($member_id); 
		
		if ($model->load(Yii::$app->request->post())) {
			$prev = (($model->other_local > 0) ? $model->other_local : 'Unspecified');
			$model->reason = Status::REASON_CCD . $prev;
			if ($this->member->addStatus($model)) {
				Yii::$app->session->addFlash('success', "{$this->getBasename()} changed for Clear In");
				return $this->goBack();
			}
			throw new \Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
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
	
	protected function initCreate($model)
	{
		if (!isset($model->lob_cd) && ($this->member->currentStatus != null))
			$model->lob_cd = $this->member->currentStatus->lob_cd;
	}
	
}
