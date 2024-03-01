<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use app\helpers\OptionHelper;
use app\models\accounting\ApfAssessment;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\InitFee;
use app\models\accounting\ReinstateAssessment;
use app\models\base\BaseEndable;
use app\models\member\ClassCode;
use app\models\member\MemberReinstateStaged;
use app\models\member\Note;
use app\models\member\ReinstateForm;
use app\models\training\Timesheet;
use Exception;
use Throwable;
use Yii;
use app\models\member\Status;
use app\models\employment\Employment;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\bootstrap\ActiveForm;

use app\models\member\Member;
use app\models\accounting\Assessment;
use app\modules\admin\models\FeeType;
use app\models\accounting\AdminFee;
use app\components\utilities\OpDate;
use yii\web\Response;

/**
 * MemberStatusController implements the CRUD actions for Status model.
 */
class MemberStatusController extends SummaryController
{

	public $recordClass = 'app\models\member\Status';
	public $relationAttribute = 'member_id';
	/**
	 * @var Member
	 */
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

    /**
     * Had to override because of the pass by value of $model.  Clean
     * this up.
     *
     * @param string $relation_id Member ID
     * @return array|mixed|string|Response
     * @throws NotFoundHttpException|Throwable
     * @see \app\controllers\base\SubmodelController::actionCreate()
     */
	public function actionCreate($relation_id)
	{
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
						} catch (Exception $e) {
							Yii::$app->session->addFlash('error', "Could not remove reinstatement fee assessment. Check if payments already made.");
						}
					}
					
				}

				if ($model->member_status == Status::INACTIVE) {
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

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
	public function actionSummaryJson($id)
	{
		$this->setMember($id);
		$status = isset($this->member->currentStatus) ? $this->member->currentStatus->member_status : Status::INACTIVE;
		$is_prepped = isset($this->member->inactiveStaged);
		$this->viewParams = ['status' => $status, 'is_prepped' => $is_prepped];
		return parent::actionSummaryJson($id);
	}

    /**
     * @param $member_id
     * @return array|string|Response
     * @throws NotFoundHttpException
     */
	public function actionReset($member_id) 
	{
		if (!Yii::$app->user->can('resetPT'))
			return $this->renderAjax('/partials/_deniedaction');

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
			    $messages[] = $this->adjustPaidThru($model->paid_thru_dt);
			}
			if (!empty($model->init_dt)) {
				$this->member->init_dt = $model->init_dt;
				$init_dt = (new OpDate)->setFromMySql($model->init_dt)->getDisplayDate(false, '/');
				$messages[] =  Status::REASON_RESET_INIT . $init_dt;
			}
			$model->reason .= implode('; ', $messages);
			if ($this->addStatus($model)) {

			    $this->saveMember($messages);

			}
			
			return $this->goBack();
			
		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
		return $this->renderAjax('create', compact('model'));
	
	}

    /**
     * @param $member_id
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
	public function actionForfeit($member_id) 
	{

        $model = new Status();
		$this->setMember($member_id);
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {

            if ($this->addStatus($model)) {
                // Remove assessments with outstanding balances
                foreach ($this->member->feeBalances as $balance)
                    Assessment::findOne($balance->id)->delete();
            }
			
			return $this->goBack();

		}
		$this->initCreate($model);
		$model->member_status = Status::INACTIVE;
		$model->reason = Status::REASON_FORFEIT;
		return $this->renderAjax('create', compact('model'));
		
	}

    /**
     * @param $member_id
     * @return array|string|Response
     * @throws NotFoundHttpException|\yii\base\Exception
     */
	public function actionSuspend($member_id) 
	{

        $model = new Status();
		$this->setMember($member_id);
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
			if ($this->addStatus($model)) {

                /** @noinspection PhpStatementHasEmptyBodyInspection */
                if (!$this->assessReinstFee($model)) { } // stub
					
			}
			
			return $this->goBack();

		}
		$this->initCreate($model);
		$model->member_status = Status::SUSPENDED;
		$model->reason = Status::REASON_SUSP;
		return $this->renderAjax('create', compact('model'));
		
	}

    /**
     * @param $member_id
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
	public function actionReinstate($member_id)
    {
        $this->setMember($member_id);
        $init = InitFee::findOne([
            'lob_cd' =>$this->member->currentStatus->lob_cd,
            'member_class' => $this->member->currentClass->member_class,
            'end_dt' => null,
        ]);
        $reinst_amt = AdminFee::getFee(FeeType::TYPE_REINST, $this->getToday()->getMysqlDate());
        $model = new ReinstateForm($this->member, $init, $reinst_amt);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $stage = new MemberReinstateStaged(['reinstate_type' => $model->type]);
            $stage->dues_owed_amt = 0.00;
            $today = $this->getToday()->getMySqlDate();
            if ($model->type == ReinstateForm::TYPE_APF) {
                // Hold application_dt for undo
                $stage->hold_application_dt = $this->member->application_dt;
                $assessModel = new ApfAssessment(['assessment_dt' => $today]);
                $assessModel->makeFromReinstate($model, $this->member);
                // By policy, immediately puts member on app when this option is selected
                $status = new Status([
                    'member_status' => Status::IN_APPL,
                    'reason' => Status::REASON_REINST,
                ]);
                $this->member->addStatus($status);
            } elseif ($model->type == ReinstateForm::TYPE_BACKDUES) {
                // Assume dues and reinstate fee are always checked
                $stage->dues_owed_amt = $model->getFee(ReinstateForm::FEE_DUES)['amt'];
                $assessModel = new ReinstateAssessment(['assessment_dt' => $today]);
                $assessModel->makeFromReinstate($model, $this->member);
                // By policy, immediately suspends when this option is selected
                $status = new Status([
                    'member_status' => Status::SUSPENDED,
                    'reason' => Status::REASON_REINST,
                ]);
                $this->member->addStatus($status);
            }

            // Setup undo keys
            if (isset($status))
                $stage->status_id = $status->id;
            if (isset($assessModel))
                $stage->assessment_id = $assessModel->id;

            if (isset($model->authority) && ($model->authority != '')) {
                $note_qty = 'Selected';
                if ($model->type == ReinstateForm::TYPE_WAIVE) {
                    $note_qty = 'All';
                    $status = new Status(['effective_dt' => $today]);
                    $status->makeReinstate($this->member);
                }
                $note_txt = "[{$note_qty} reinstatement fees WAIVED by {$model->getAuthUser()->username}]";
                $note = new Note(['note' => $note_txt]);
                $this->member->addNote($note);
            }

            if ($model->type != ReinstateForm::TYPE_WAIVE)
                $this->member->addReinstateStaged($stage);

            return $this->goBack();
        }

        $model->assessments_b = [ReinstateForm::FEE_DUES, ReinstateForm::FEE_REINST];
        return $this->renderAjax('reinstate', ['model' => $model]);
    }

    /**
     * @param $member_id
     * @return Response
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionCancelReinstate($member_id)
    {
        $staged = MemberReinstateStaged::findOne($member_id);
        if ($assessment = $staged->assessment) {
            if (AssessmentAllocation::find()->where(['assessment_id' => $assessment->id])->count() > 0) {
                $note = new Note(['note' => 'Reinstatement cancelled. Partial payment(s) forfeited.']);
                $staged->member->addNote($note);
            }
            $assessment->delete();
        }
        if ($status = $staged->status) {
            if ($status->delete())
                Status::openLatest($member_id);
        }
        if (isset($staged->hold_application_dt)) {
            $member = $staged->member;
            $member->application_dt = $staged->hold_application_dt;
            $member->save();
        }
        if ($staged->delete())
            Yii::$app->session->addFlash('success', 'Reinstatement successfully cancelled');
        else
            Yii::$app->session->addFlash('error', 'Problem with reinstate cancel. Requires manual process');
        return $this->goBack();
    }

    /**
     * @param $member_id
     * @return array|string|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
	public function actionClearIn($member_id) 
	{
        $model = new Status(['scenario' => Status::SCENARIO_CCD]);
		$this->setMember($member_id); 
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
		    $curr = $model->lob_cd;
			$prev = (($model->other_local > 0) ? $model->other_local : 'Unspecified');
			$model->reason = Status::REASON_CCD . $prev;

            if ($this->addStatus($model)) {
                if (!empty($model->paid_thru_dt)) {
                    $this->saveMember($this->adjustPaidThru($model->paid_thru_dt));
                }
                if (isset($this->member->currentClass) && $this->member->currentClass->member_class == ClassCode::CLASS_APPRENTICE)
                    if (array_key_exists($prev, $model->getLobOptions()))
                    {
                        Timesheet::archiveByTrade($member_id, $prev, OptionHelper::TF_FALSE);
                        if (Timesheet::restoreByTrade($member_id, $curr) > 0) {
                            $msg = "DPR timesheets found and restored for trade `{$curr}`";
                            Yii::$app->session->addFlash('success', $msg);
                        }
                    }
            }

			return $this->goBack();

		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
		return $this->renderAjax('create', compact('model'));
		
	}

    /**
     * @param $member_id
     * @return array|string|Response
     * @throws NotFoundHttpException
     */
	public function actionDepIsc($member_id) 
	{
        $model = new Status();
		$this->setMember($member_id); 
		
		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			$model->member_id = $this->member->member_id;
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
		if ($model->load(Yii::$app->request->post())) {
			$model->reason = Status::REASON_DEPINSVC;

            /** @noinspection PhpStatementHasEmptyBodyInspection */
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

    /**
     * Do not allow deletion if last remaining endable
     * @param BaseEndable $model
     * @return bool
     */
    protected function canDelete(BaseEndable $model)
    {
        if ($model->isOnlyOccurrence())
            return false;
        return parent::canDelete($model);
    }

    /**
     * @param Status $model
     * @return bool
     * @throws \yii\base\Exception
     */
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

    /**
     * @param $paid_thru_dt
     * @return string
     */
	protected function adjustPaidThru($paid_thru_dt)
    {
        $this->member->dues_paid_thru_dt = $paid_thru_dt;
        $pt_dt = (new OpDate)->setFromMySql($paid_thru_dt)->getDisplayDate(false, '/');
        return Status::REASON_RESET_PT . $pt_dt;
    }

    /**
     * @param $msg_content
     */
    protected function saveMember($msg_content)
    {
        $banner = is_array($msg_content) ? implode('; ', $msg_content) : $msg_content;
        if ($this->member->save())
            Yii::$app->session->addFlash('success', $banner);
        else {
            Yii::$app->session->addFlash('error', 'Problem saving Member. Check log for details. Code `MSC010`');
            Yii::error("*** MSC010  Member save error (`{$this->member->member_id}`).  Messages: " . print_r($this->member->errors, true));
        }

    }

    /**
     * Override this function when testing with fixed date
     *
     * @return OpDate
     */
    public function getToday()
    {
        return new OpDate();
    }

}
