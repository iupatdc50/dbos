<?php

namespace app\controllers;

use Yii;
use app\models\member\Member;
use app\models\accounting\Assessment;
use app\models\accounting\DuesRateFinder;
use app\models\member\Standing;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\helpers\Json;
use yii\data\yii\data;

class MemberBalancesController extends Controller
{
	
	public function actionSummaryJson($id)
	{
		$member = Member::findOne($id);
		
		$messages = [];
		if(!isset($member->currentStatus))
			$messages[] = 'Cannot identify local union.  Check Status panel.';
		if(!isset($member->currentClass))
			$messages[] = 'Cannot identify rate class.  Check Class panel.';
		if (empty($messages)) {		
			$rate_finder = new DuesRateFinder($member->currentStatus->lob_cd, $member->currentClass->rate_class);
			$standing = new Standing(['member' => $member]);
			$dues_balance = number_format($standing->getDuesBalance($rate_finder), 2);
			$assessment_balance = number_format($standing->totalAssessmentBalance, 2); 
			
			$query = Assessment::find()->joinWith('allocatedPayments');
			$query->where(['member_id' => $member->member_id]);
			$assessProvider = new ActiveDataProvider([
					'query' => $query,
			]);
			
			echo Json::encode($this->renderPartial('_balances', [
					'member' => $member,
					'dues_balance' => $dues_balance,
					'assessment_balance' => $assessment_balance,
					'assessProvider' => $assessProvider,
			]));
		} else {
			echo Json::encode(implode(PHP_EOL, $messages));
		}
	}
	
	
}