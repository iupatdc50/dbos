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
		
		if ($model->load(Yii::$app->request->post())) {
			$pt_dt = (new OpDate)->setFromMySql($model->paid_thru_dt)->getDisplayDate(false, '/');
			if (isset($model->reason))
				$model->reason .= PHP_EOL;
			$model->reason .= Status::REASON_RESET . $pt_dt;
			if ($this->member->addStatus($model)) {
				Yii::$app->session->addFlash('success', "{$this->getBasename()} activated");
				$this->member->dues_paid_thru_dt = $model->paid_thru_dt;
				if ($this->member->save()) {
					Yii::$app->session->addFlash('success', "Dues Thru Date reset to {$pt_dt}");
					return $this->goBack();
				}
				throw new \Exception	('Problem with post.  Errors: ' . print_r($this->member->errors, true));
			}
			throw new \Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
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
				Yii::$app->session->addFlash('error', 'Problem saving assessment. Check log for details. Code `MSC010`'); 
				Yii::error("*** MSC010  member-status-controller/drop(`{$member_id}`).  Messages: " . print_r($assessModel->errors, true));
			} else {
				Yii::$app->session->addFlash('error', 'Problem adding Member Status. Check log for details. Code `MSC020`');
				Yii::error("*** MSC020  member-status-controller/drop(`{$member_id}`).  Messages: " . print_r($model->errors, true));
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
