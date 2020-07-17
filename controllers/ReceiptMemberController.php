<?php

namespace app\controllers;

use app\models\accounting\Transaction;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\Stripe;
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
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReceiptMemberController extends BaseController
{

    /**
     * Displays a single Receipt model.
     * @param integer $id
     * @return mixed
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
     * Stages credit card payment dialog
     *
     * @param $id string Member ID
     * @return string
     */
    public function actionCreditCard($id)
    {
        $member = Member::findOne($id);

        $total_due = $member->allBalance->total_due;
        if($total_due < 0.00)
            $total_due = 0.00;

        $payment_data = [
            'member_id' => $member->member_id,
            'lob_cd' => $member->currentStatus->lob_cd,
            'currency' => 'usd',
            'charge' => $total_due,
            'email' => isset($member->emails[0]) ? $member->emails[0]->email : null,
            'cardholder_nm' => $member->first_nm . ' ' . $member->last_nm,
            'has_ccg' => $member->ccgBalanceCount > 0,
        ];

        return $this->renderAjax('creditcard', ['payment_data' => $payment_data]);
    }

    /**
     * Processes credit card payment and builds an unposted receipt
     *
     * @return Response
     * @throws Exception
     * @noinspection PhpRedundantCatchClauseInspection
     *
     * @todo Sanitize post()
     */
    public function actionPayment()
    {
        $filters = [
            'email' => FILTER_SANITIZE_EMAIL,
            'charge' => [FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION],
            'member_id' => FILTER_SANITIZE_STRING,
            'cardholder_nm' => FILTER_SANITIZE_STRING,
            'currency' => FILTER_SANITIZE_STRING,
            'stripe_token' => FILTER_SANITIZE_STRING,
        ];
        $post = filter_var_array(Yii::$app->request->post(), $filters);

        $member = Member::findOne($post['member_id']);

        if (!isset($post['stripe_token'])) {
            Yii::error("*** RMC020 Member `{$member->member_id}` {$member->fullName} did not return CC verification token");
            Yii::$app->session->addFlash('error', 'Unable to process credit card [Code: RMC020]');
            return $this->goBack();
        }

        Stripe::setApiKey(Yii::$app->params['stripe'][$member->currentStatus->lob_cd]['secret_key']);

        $tracking = Transaction::getTracking();

        $msg = null;
        $db_trans = Yii::$app->db->beginTransaction();
        try {
            if (!isset($member->stripe_id)) {
                $customer = Customer::create([
                    'email' => $post['email'],
                    'name' => $post['cardholder_nm'],
                    'source' => $post['stripe_token'],
                ]);
                $member->stripe_id = $customer->id;
                $member->save();
            } else
                $customer = Customer::update($member->stripe_id, [
                    'source' => Yii::$app->request->post()['stripe_token'],
                ]);

            $charge = Charge::create([
                'amount' => $post['charge'] * 100,
                'currency' => $post['currency'],
                'description' => 'DC50 in-office payment',
                'receipt_email' => $post['email'],
                'customer' => $customer->id,
                'metadata' => ['tracking' => $tracking, 'trade' => $member->currentStatus->lob_cd],
            ]);

            $receipt = new ReceiptMember();
            if (($receipt_id = $receipt->makeUnposted($post, $customer, $charge)) != false) {
                $transaction = new Transaction([
                    'transaction_id' => $charge->id,
                    'customer_id' => $customer->id,
                    'tracking_nbr' => $tracking,
                    'receipt_id' => $receipt_id,
                    'member_id' => $member->member_id,
                    'currency' => $charge->currency,
                    'charge' => $post['charge'],
                    'created_at' => $charge->created,
                    'stripe_status' => $charge->status,
                    'dbos_status' => $charge->status == Transaction::STRIPE_SUCCEEDED ? Transaction::DBOS_INPROGRESS : $charge->status,
                ]);
                $transaction->save();
                $db_trans->commit();
                // Forces user to manually post
                return $this->redirect(['update', 'id' => $receipt_id]);
            }

            $db_trans->rollBack();

        } catch (CardException $e) {
            $msg = 'Payment unsuccessful: ' . $e->getError()->message;
        } catch (RateLimitException $e) {
            $msg = 'You made too many requests made too quickly.  Please try again later.';
        } catch (InvalidRequestException $e) {
            Yii::error('Tracking: ' . $tracking . ' Error: ' . print_r($e->getError()));
            $msg = 'Internal error: ' . $e->getError()->code;
        } catch (AuthenticationException $e) {
            Yii::error('Tracking: ' . $tracking . ' Error: ' . print_r($e->getError()));
            $msg = 'Internal error: ' . $e->getError()->code;
        } catch (ApiConnectionException $e) {
            $msg = 'Connectivity problems or network interruption.  Please try again later.';
        } catch (ApiErrorException $e) {
            Yii::error('Tracking: ' . $tracking . ' Error: ' . print_r($e->getError()));
            $msg = 'Internal error: ' . $e->getError()->code;
        } catch (\yii\base\Exception $e) {
            Yii::error('Tracking: ' . $tracking . ' Error: ' . print_r($e->getMessage()));
            $msg = 'Internal error: ' . $e->getMessage();
        }
        Yii::$app->session->addFlash('error', $msg);
        $db_trans->rollBack();
        return $this->goBack();
    }


    /**
     *
     * @param $lob_cd
     * @param null $id
     * @return string
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
						throw new \Exception("Error when trying to stage Allocated Member `{$modelMember->errors}`");
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
            $model->received_amt = number_format($member->allBalance->total_due, 2);
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
		$this->storeReturnUrl();
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

        /** @noinspection SqlResolve */
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