<?php

namespace app\controllers;

use app\helpers\ExceptionHelper;
use app\models\accounting\RefundForm;
use app\models\accounting\StripePaymentManagerCharge;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use app\controllers\receipt\BaseController;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMember;
use app\models\accounting\ReceiptMemberSearch;
use app\models\accounting\AllocatedMember;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AllocationBuilder;
use app\models\member\Member;
use app\models\accounting\CcOtherLocal;
use yii\data\SqlDataProvider;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReceiptMemberController extends BaseController
{

    /**
     * Displays a single Receipt model.
     * @param integer $id
     * @return Response|string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
    	$model = $this->findModel($id);

    	/* @var $model ReceiptMember */
    	if ($model->isUpdating())
    	    return $this->redirect([
    	        'update',
                'id' => $model->id,
            ]);

        if($model->outOfBalance != 0.00)
            return $this->redirect([
                'itemize',
                'id' => $model->id,
            ]);

        $allocProvider = $this->buildAllocProvider($id);
    	 
    	return $this->render('view', compact('model', 'allocProvider'));
    }

    /**
     *
     * @param $lob_cd
     * @param null $id
     * @return string|Response
     * @throws \Exception
     */
	public function actionCreate($lob_cd, $id = null)
	{
		$model = new ReceiptMember(['scenario' => Receipt::SCENARIO_CREATE]);
		if (!isset($lob_cd))
			throw new \Exception('lob_cd is required');
		$model->lob_cd = $lob_cd;
		
		$modelMember = new AllocatedMember();
		if (isset($id))
			$modelMember->member_id = $id;

		if ($model->load(Yii::$app->request->post()) && $modelMember->load(Yii::$app->request->post())) {
			$model->payor_type = Receipt::PAYOR_MEMBER;
			if (empty($model->payor_nm)) 
				$model->payor_nm = Member::findOne($modelMember->member_id)->fullName;
				
			$transaction = Yii::$app->db->beginTransaction();
			try {
				if ($model->save(false)) {
					$modelMember->receipt_id = $model->id;
					if (!$modelMember->save())
						throw new \Exception("Error when trying to stage Allocated Member `$modelMember->errors`");
					$builder = new AllocationBuilder();
					$result = $builder->prepareAllocs($modelMember, $model->fee_types);
					if ($result != true)
						throw new \Exception('Uncaught validation errors: ' . $result);
					if ($model->other_local > 0)
					    $modelMember->addOtherLocal(new CcOtherLocal(['other_local' => $model->other_local]));
					$transaction->commit();
					return $this->redirect(['itemize', 'id' => $model->id]); 
				}
				$transaction->rollBack();
			} catch (\Exception $e) {
				$transaction->rollBack();
				throw new \Exception('Error when trying to save created Receipt: ' . $e);
			}
		} 
		
		if (!isset($model->received_amt) && isset($modelMember->member_id)) {
		    $member = $modelMember->member;
            $model->received_amt = number_format($member->allBalance, 2);
        }

		if (Yii::$app->request->isAjax)
			return $this->renderAjax('create', [
					'model' => $model,
					'modelMember' => $modelMember,
			]);
				
		return $this->render('create', [
				'model' => $model,
				'modelMember' => $modelMember,
		]);
		
	}

    /**
     * @param $id integer Receipt ID
     * @return string
     * @throws NotFoundHttpException
     */
	public function actionItemize($id)
	{
		$modelReceipt = $this->findModel($id);
		$allocProvider = $this->buildAllocProvider($id);
		return $this->render('itemize', [
				'modelReceipt' => $modelReceipt,
				'allocProvider' => $allocProvider,
		]);
	}

	public function actionUpdate($id)
    {
        // Assume 1 allocated member on a member receipt
        $this->config['allocProvider'] = $this->buildAllocProvider($id);

        return parent::actionUpdate($id);
    }

    /**
     * @param $id
     * @return Response|string
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionRefund($id)
    {
        /* @var $receipt ReceiptMember */
        $receipt = $this->findModel($id);
        $manager = new StripePaymentManagerCharge(['member' => $receipt->payingMember]);
        $model = new RefundForm();

        if ($model->load(Yii::$app->request->post())) {
            if (($refund = $manager->createRefund($model->charge_id)) != false) {
                $transaction = $receipt->transaction;
                $transaction->refund_id = $refund->id;
                $transaction->save();
                $result = $receipt->void(Yii::$app->user->identity->username, true);

                if (empty($result)) {
                    Yii::$app->session->setFlash('success', "Charge refunded and receipt successfully voided");
                    return $this->redirect(['view', 'id' => $model->receipt_id]);
                }
                // Error handling
            }
            foreach ($manager->messages as $code => $message)
                ExceptionHelper::handleError(Yii::$app->session, $code, $message['friendly'], $message['system']);

        }

        $model->receipt_id = $receipt->id;
        $model->charge_id = $receipt->transaction->transaction_id;
        if (($charge = $manager->getCharge($model->charge_id)) == false) {
            foreach ($manager->messages as $code => $message)
                ExceptionHelper::handleError(Yii::$app->session, $code, $message['friendly'], $message['system']);
            return $this->goBack();
        }

        return $this->renderAjax('refund', [
            'model' => $model,
            'receipt' => $receipt,
            'card' => $charge->source,
        ]);
    }

    public function actionSummaryAjax($id)
	{
		$searchModel = new ReceiptMemberSearch();
		$searchModel->member_id = $id;
        /** @noinspection PhpUndefinedMethodInspection */
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		return $this->renderAjax('_summary', [
				'dataProvider' => $dataProvider,
				'searchModel' => $searchModel,
				'payorPicklist' => Receipt::getPayorOptions(),
		]);
	}

    /**
     * @param $member_id
     * @return Response
     * @throws Exception
     */
	public function actionSummFlattenedJson($member_id)
    {
        if (!Yii::$app->user->can('browseReceipt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));

        $typesSubmitted = ReceiptMember::getFeeTypesSubmitted($member_id);

        $count = Yii::$app->db->createCommand(
            'SELECT COUNT(*) FROM AllocatedMembers WHERE member_id = :member_id',
            [':member_id' => $member_id]
        )->queryScalar();

        $sqlProvider = new SqlDataProvider([
            'sql' => ReceiptMember::getFlattenedReceiptsByMemberSql($typesSubmitted),
            'params' => [':member_id' => $member_id],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->asJson($this->renderAjax('_summflattened', [
            'sqlProvider' => $sqlProvider,
            'typesSubmitted' => $typesSubmitted,
        ]));
    }

	protected function buildAllocProvider($id)
	{
		$query = BaseAllocation::find()->joinWith(['allocatedMember'])->where(['receipt_id' => $id])->orderBy('fee_type');
		return new ActiveDataProvider(['query' => $query]);		
	}
}