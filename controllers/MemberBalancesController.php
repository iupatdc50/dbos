<?php

namespace app\controllers;

use app\models\accounting\DuesAllocation;
use app\models\accounting\DuesAllocPickList;
use app\models\accounting\StatusManagerDues;
use app\models\member\OverageHistory;
use app\models\member\RepairDuesForm;
use app\models\member\Status;
use Yii;
use app\models\member\Member;
use app\models\accounting\DuesRateFinder;
use app\models\member\Standing;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\helpers\Json;
use yii\web\Response;

class MemberBalancesController extends Controller
{
    /**
     * @param $id
     * @throws \yii\db\Exception
     */
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
                $standing = new Standing(['member' => $member]);

                if ($member->currentStatus->member_status == Status::OUTOFSTATE)
                    $dues_balance = number_format(0.00, 2);
                else {
                    $rate_finder = new DuesRateFinder($member->currentStatus->lob_cd, $member->currentClass->rate_class);
                    $dues_balance = number_format($standing->getDuesBalance($rate_finder), 2);
                }
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
            'sort' => ['defaultOrder' => ['received_dt'=>SORT_DESC, 'receipt_id'=>SORT_DESC]],
        ]);

        return $this->renderAjax('_dueshistory', ['dataProvider' => $dataProvider]);
    }


    /**
     * @param $id
     * @return array|string|Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionRepairDues($id)
    {
        $member = Member::findOne($id);
        $model = new RepairDuesForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $faulties = DuesAllocPickList::find()
                ->where(['member_id' => $member->member_id])
                ->andWhere(['>', 'id', $model->alloc_id])
                ->orderBy('id')
                ->all()
            ;

            if(($count = count($faulties)) == 0) {
                Yii::$app->session->addFlash('notice', 'Nothing to repair');
                return $this->goBack();
            }

            // stage starting paid thru date & overage
            $member->dues_paid_thru_dt = $model->paid_thru_dt;
            $member->overage = $model->overage;
            if($member->save())
                $this->resetOverageHistory($member);

            $errors = [];
            $receipt_id = null;
            foreach ($faulties as $faulty) {
                $alloc = $faulty->allocation;
                $alloc->duesRateFinder = new DuesRateFinder(
                    $member->currentStatus->lob_cd,
                    isset($member->currentClass) ? $member->currentClass->rate_class : 'R'
                );
                $standing = new Standing(['member' => $member]);
                $manager = new StatusManagerDues(['standing' => $standing]);
                $errors = array_merge($errors, $manager->applyDues($alloc));
                $receipt_id = $faulty->receipt_id;
            }

            if (empty($errors)) {
                if ($member->overage <> 0.00) {
                    $this->resetOverageHistory($member);
                    $history = new OverageHistory([
                        'member_id' => $member->member_id,
                        'dues_paid_thru_dt' => $member->dues_paid_thru_dt,
                        'receipt_id' => $receipt_id,
                        'overage' => $member->overage,
                    ]);
                    $member->addOverageHistory($history);
                }

                $adj = ($count == 1) ? '1 allocation' : $count . ' allocations';
                Yii::$app->session->addFlash('success', "Repair completed. {$adj} adjusted");

            } else {
                Yii::$app->session->addFlash('error', "Problem with repair.  Check log for details. Code `MBC010`");
                Yii::error("*** MBC010: Problem with repair.  Errors: " . print_r($errors, true));
            }

            return $this->goBack();

        }
        return $this->renderAjax('repair', ['model' => $model, 'member' => $member]);

    }

    /**
     * List builder for dues alloc pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If an ID number is provided,
     *               then this key provides the allocation ID and picklist descrip
     *
     * @param string|array $search Criteria used.
     * @param null $member_id
     * @param string $id Selected allocation ID
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionAllocList($search = null, $member_id = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($search)) {
            $condition = (is_null($member_id)) ? $search : ['descrip' => $search, 'member_id' => $member_id];
            $data = DuesAllocation::listAll($condition);
            $out['results'] = array_values($data);
        }
        elseif (!is_null($id) && ($id <> '0')) {
            $out['results'] = ['alloc_id' => $id, 'text' => DuesAllocPickList::findOne($id)->descrip];
        }
        return $out;
    }

    /**
     * @param $alloc_id
     * @return array JSON encoded overage value associated with the allocation
     */
    public function actionGetBase($alloc_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $alloc = DuesAllocPickList::findOne($alloc_id);
        return [
            'paid_thru_dt' => $alloc->paid_thru_dt,
            'overage' => $alloc->overage,
        ];
    }

    /**
     * Assumes that $member contains the starting dues thru and overage
     *
     * @param Member $member
     * @throws \yii\db\StaleObjectException
     */
    public function resetOverageHistory(Member $member)
    {
        $history = OverageHistory::find()
            ->where(['member_id' => $member->member_id])
            ->andWhere(['>', 'dues_paid_thru_dt', $member->dues_paid_thru_dt])
            ->all();
        foreach ($history as $overage)
            $overage->delete();
    }

}

