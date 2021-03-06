<?php

namespace app\controllers;

use app\models\accounting\DuesAllocation;
use app\models\accounting\DuesAllocPickList;
use app\models\accounting\StatusManagerDues;
use app\models\member\OverageHistory;
use app\models\member\RepairDuesForm;
use Throwable;
use Yii;
use app\models\member\Member;
use app\models\member\Standing;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\Response;

class MemberBalancesController extends Controller
{
    /**
     * @param $id
     * @return Response
     */
	public function actionSummaryJson($id)
	{
        if (!Yii::$app->user->can('browseReceipt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));

        $member = Member::findOne($id);

        $messages = [];
        if (!isset($member->currentStatus))
            $messages[] = 'Cannot identify local union.  Check Status panel.';
        if (!isset($member->currentClass))
            $messages[] = 'Cannot identify rate class.  Check Class panel.';
        if (empty($messages)) {

            $assessProvider = new ActiveDataProvider([
                'query' => $member->getAssessments(),
                'sort' => ['defaultOrder' => ['assessment_dt' => SORT_DESC]],
            ]);

            $apf = $member->currentApf;
            if ($member->isInApplication() && (!isset($apf)))
                Yii::$app->session->setFlash('balance', 'Member is in application but has no current APF assessment.  Balance due may be incorrect.');

            return $this->asJson($this->renderPartial('_balances', [
                'member' => $member,
                'assessProvider' => $assessProvider,
            ]));
        } else {
            return $this->asJson(implode(PHP_EOL, $messages));
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
     * @throws Exception
     * @throws Throwable
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
                ->orderBy(['received_dt' => SORT_ASC, 'id' => SORT_ASC])
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
                /* @var $faulty DuesAllocPickList */
                $alloc = $faulty->allocation;
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
     * @throws Exception
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
     * @throws StaleObjectException
     * @throws Throwable
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

