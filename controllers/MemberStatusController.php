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
use app\models\accounting\Assessment;
use app\modules\admin\models\FeeType;
use app\models\accounting\AdminFee;

/**
 * MemberStatusController implements the CRUD actions for Status model.
 */
class MemberStatusController extends SummaryController
{

	public $recordClass = 'app\models\member\Status';
	public $relationAttribute = 'member_id';
	public $member;
	
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
		
		return $this->renderAjax('/site/unavailable');
	
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
				throw new \Exception	('Problem with post.  Errors: ' . print_r($assessModel->errors, true));
			}
			throw new \Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		$this->initCreate($model);
		$model->member_status = Status::INACTIVE;
		$model->reason = Status::REASON_DROP;
		return $this->renderAjax('create', compact('model'));
		
	}
	
	public function actionClearIn($member_id) 
	{	
		/** @var Model $model */
		$model = new Status();
		$this->setMember($member_id); 
		
		if ($model->load(Yii::$app->request->post())) {
			$prev = (($model->other_local > 0) ? $model->other_local : 'Unspecified');
			$model->reason .= $prev;
			if ($this->member->addStatus($model)) {
				Yii::$app->session->addFlash('success', "{$this->getBasename()} changed for Clear In");
				return $this->goBack();
			}
			throw new \Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		$this->initCreate($model);
		$model->member_status = Status::ACTIVE;
		$model->reason = Status::REASON_CCD;
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
