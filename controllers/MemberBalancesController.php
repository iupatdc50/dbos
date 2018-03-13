<?php

namespace app\controllers;

use Yii;
use app\models\member\Member;
use app\models\accounting\DuesRateFinder;
use app\models\member\Standing;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\helpers\Json;

class MemberBalancesController extends Controller
{
	
	public function actionSummaryJson($id)
	{
        if (!Yii::$app->user->can('browseReceipt')) {
            echo Json::encode($this->renderAjax('/partials/_deniedview'));
        } else {

            $member = Member::findOne($id);

            $messages = [];
            if (!isset($member->currentStatus))
                $messages[] = 'Cannot identify local union.  Check Status panel.';
            if (!isset($member->currentClass))
                $messages[] = 'Cannot identify rate class.  Check Class panel.';
            if (empty($messages)) {
                $rate_finder = new DuesRateFinder($member->currentStatus->lob_cd, $member->currentClass->rate_class);
                $standing = new Standing(['member' => $member]);
                $dues_balance = number_format($standing->getDuesBalance($rate_finder), 2);
                $assessment_balance = number_format($standing->totalAssessmentBalance, 2);

                $assessProvider = new ActiveDataProvider([
                    'query' => $member->getAssessments(),
                    'sort' => ['defaultOrder' => ['assessment_dt' => SORT_DESC]],
                ]);

                $apf = $member->currentApf;
                if ($member->isInApplication() && (!isset($apf)))
                    Yii::$app->session->setFlash('balance', 'Member is in application but has no current APF assessment.  Balance due may be incorrect.');

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
	
	public function actionDuesSummaryAjax($id)
    {
        $member = Member::findOne($id);

        $dataProvider = new ActiveDataProvider([
            'query' => $member->getDuesAllocations(),
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['received_dt'=>SORT_DESC]],
        ]);

        return $this->renderAjax('_dueshistory', ['dataProvider' => $dataProvider]);
    }
}

