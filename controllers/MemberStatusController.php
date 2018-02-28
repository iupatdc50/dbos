<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use app\helpers\ClassHelper;
use Yii;
use app\models\member\Status;
use app\models\member\Employment;
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
						'only' => ['create', 'forfeit', 'drop', 'clear-in'],
						'rules' => [
								[
										'allow' => true,
										'actions' => ['create', 'forfeit', 'drop', 'clear-in', 'dep-insvc'],
										'roles' => ['createMember', 'updateMember'],
								],
								[
										'allow' => true,
										'actions' => ['reset'],
										'roles' => ['resetPT'],
								],
						],
				],
	
		];
	}
	
	public function actionCreate($relation_id)
	{
		/** @var Status $model */
		$model = new Status();
		$this->setMember($relation_id);
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
			if ($this->addStatus($model)) {
				
				if ($model->member_status == Status::IN_APPL) {
					$this->member->application_dt = $model->effective_dt;
					$this->member->save();
					Yii::$app->session->addFlash('success', "APF assessment created");
					/** @var Assessment $assessment */
					$assessment = Assessment::findOne([
							'member_id' => $this->member->member_id,
							'fee_type' => FeeType::TYPE_REINST,
					]);
					if (isset($assessment)) {
						try {
							$assessment->delete();
							Yii::$app->session->addFlash('success', "Reinstatement fee assessment removed");
						} catch (\Exception $e) {
							Yii::$app->session->addFlash('error', "Could not remove reinstatement fee assessment. Check if payments already made.");
						}
					}
					
				}

				if ($model->member_status == Status::INACTIVE) {
                    /** @var Employment $employer */
				    $employer = $this->member->employerActive;
				    if (isset($employer)) {
				        $employer->end_dt = $model->effective_dt;
				        $employer->term_reason = Employment::TERM_MEMBER;
				        $employer->save();
                    }
                }
					
			}
			
			return $this->goBack();
				
		}
		$this->initCreate($model);
		return $this->renderAjax('create', compact('model'));
		
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
		
		/** @var Status $model */
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
			if ($this->addStatus($model)) {
				
				if ($this->member->save()) 
					Yii::$app->session->addFlash('success', implode('; ', $messages));
				else {
					Yii::$app->session->addFlash('error', 'Problem saving Member. Check log for details. Code `MSC010`'); 
					Yii::error("*** MSC010  Member save error (`{$member_id}`).  Messages: " . print_r($this->member->errors, true));
				}
					
			}
			
			return $this->goBack();
			
		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
		return $this->renderAjax('create', compact('model'));
	
	}
	
	public function actionForfeit($member_id) 
	{
	
		/** @var Status $model */
		$model = new Status();
		$this->setMember($member_id);
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
			if ($this->addStatus($model)) { } // stub
			
			return $this->goBack();

		}
		$this->initCreate($model);
		$model->member_status = Status::INACTIVE;
		$model->reason = Status::REASON_FORFEIT;
		return $this->renderAjax('create', compact('model'));
		
	}
	
	public function actionSuspend($member_id) 
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
			if ($this->addStatus($model)) {
				
				if (!$this->assessReinstFee($model)) { } // stub
					
			}
			
			return $this->goBack();
			
		}
		$this->initCreate($model);
		$model->member_status = Status::SUSPENDED;
		$model->reason = Status::REASON_SUSP;
		return $this->renderAjax('create', compact('model'));
		
	}
	
	public function actionClearIn($member_id) 
	{	
		/** @var Model $model */
		$model = new Status(['scenario' => Status::SCENARIO_CCD]);
		$this->setMember($member_id); 
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
			$prev = (($model->other_local > 0) ? $model->other_local : 'Unspecified');
			$model->reason = Status::REASON_CCD . $prev;
			
			if ($this->addStatus($model)) { } // stub
			
			return $this->goBack();

		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
		return $this->renderAjax('create', compact('model'));
		
	}
	
	public function actionDepIsc($member_id) 
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
			$model->reason = Status::REASON_DEPINSVC;
			
			if ($this->addStatus($model)) { } // stub

			return $this->goBack();

		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
		return $this->renderAjax('create', compact('model'));
		
	}

    /**
     * Allows for injection of $this->member
     * @param string $id
     * @return Member
     * @throws NotFoundHttpException
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
	
	protected function assessReinstFee(Status $model)
	{
		$action = ($model->member_status == Status::SUSPENDED) ? 'Suspended' : 'Dropped'; 
		$assessModel = new Assessment([
				'fee_type' => FeeType::TYPE_REINST,
				'assessment_dt' => $model->effective_dt,
				'assessment_amt' => AdminFee::getFee(FeeType::TYPE_REINST, $model->effective_dt),
				'purpose' => $action . ' on this date',
		]);
		if ($this->member->addAssessment($assessModel))  {
			Yii::$app->session->addFlash('success', "Reinstate fee of {$assessModel->assessment_amt} assessed");
			return true;
		}
		
		Yii::$app->session->addFlash('error', 'Problem saving assessment. Check log for details. Code `MSC020`');
		Yii::error("*** MSC020  Assessment save error (`{$this->member->member_id}`).  Messages: " . print_r($assessModel->errors, true));
		return false;
	
	}
	
	protected function addStatus($model)
	{
		$result = $this->member->addStatus($model);
		if (strlen($result) > 1)
			Yii::$app->session->addFlash('error', $result);
		elseif ($result) 
			Yii::$app->session->addFlash('success', "{$this->getBasename()} entry added for {$model->status->descrip}");
		else {
			Yii::$app->session->addFlash('error', 'Problem saving Status. Check log for details. Code `MSC010`');
			Yii::error("*** MSC010  Status save error (`{$this->member->member_id}`).  Messages: " . print_r($model->errors, true));
		}
		return (strlen($result) > 1) ? false : $result;
	}
	
}
