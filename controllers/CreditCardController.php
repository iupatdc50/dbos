<?php

namespace app\controllers;

use app\components\utilities\OpDate;
use app\helpers\ExceptionHelper;
use app\models\accounting\CreditCardUpdateForm;
use app\models\accounting\DuesRate;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMember;
use app\models\accounting\StripeEndpointManager;
use app\models\accounting\StripePaymentManager;
use app\models\accounting\StripePaymentManagerCharge;
use app\models\accounting\StripePaymentManagerSubscription;
use app\models\accounting\StripeTransaction;
use app\models\member\Member;
use app\models\member\Subscription;
use app\models\value\Lob;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;
use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\bootstrap\ActiveForm;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CreditCardController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'handle-webhook1791' => ['post'],
                    'handle-webhook1889' => ['post'],
                    'handle-webhook1926' => ['post'],
                    'handle-webhook1944' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @param Action $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (substr($action->id, 0, 14) == 'handle-webhook')
            $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @param string $id member_id
     * @return Response
     * @throws ApiErrorException
     * @throws NotFoundHttpException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function actionSummaryJson($id)
    {
        if (!Yii::$app->user->can('browseReceipt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));

        $member = $this->findMember($id);

        $messages = $member->checkProfile();
        if (empty($messages)) {

            $subscription = $member->subscription;
            if (isset($subscription)) {
                $stripe = $this->getStripeClient($member->currentStatus->lob_cd);
                $stripe_subs = $stripe->subscriptions->retrieve($subscription->stripe_id);
                $customer = $stripe->customers->retrieve($member->stripe_id, ['expand' => ['default_source']]);
                $plan = $stripe_subs->items->data[0]->plan;
                $product = $stripe->products->retrieve($stripe_subs->items->data[0]->price->product);
                $card = $customer->default_source;
                return $this->asJson($this->renderPartial('_summary', [
                    'stripe_subs' => $stripe_subs,
                    'plan' => $plan,
                    'product' => $product,
                    'card' => $card,
                    'expired' => $this->cardExpired($card->exp_month, $card->exp_year),
                    'member_id' => $member->member_id,
                ]));

            }
            return $this->asJson($this->renderPartial('_notenrolled', ['member_id' => $member->member_id]));

        }

        return $this->asJson(implode(PHP_EOL, $messages));

    }

    /**
     * Stages credit card payment dialog
     *
     * @param $id string Member ID
     * @return string
     * @throws ApiErrorException
     */
    public function actionApprove($id)
    {
        $member = Member::findOne($id);
        /*
         * If member has an existing card on file but the approve-existing view 'GET' request
         * passes <"another" = true> parameter, clear the member's stripe_id for this session
         * to force rendering the approve-new view
         */
        if (array_key_exists('another', Yii::$app->request->get())) {
            $member->stripe_id = null;
        }

        $total_due = $member->allBalance;
        if($total_due < 0.00)
            $total_due = 0.00;
        $has_ccg = ($member->ccgBalanceCount > 0);

        if (isset($member->stripe_id)) {
            $stripe = $this->getStripeClient($member->currentStatus->lob_cd);
            $customer = $stripe->customers->retrieve($member->stripe_id, ['expand' => ['default_source']]);
            if (isset($customer->default_source)) {
                $card = $customer->default_source;
                return $this->renderAjax('approve-existing', [
                    'member' => $member,
                    'has_ccg' => $has_ccg,
                    'total_due' => $total_due,
                    'logo_path' => Yii::getAlias('@webroot') . Yii::$app->params['logoDir'],
                    'customer' => $customer,
                    'expired' => $this->cardExpired($card->exp_month, $card->exp_year),
                ]);
            }
        }

        return $this->renderAjax('approve-new', [
            'member' => $member,
            'has_ccg' => $has_ccg,
            'total_due' => $total_due,
        ]);
    }

    /**
     * @param string $id member_id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws ApiErrorException
     */
    public function actionEnroll($id)
    {
        if (!Yii::$app->user->can('createReceipt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));

        $member = $this->findMember($id);
        /*
         * If member has an existing card on file but the approve-existing view 'GET' request
         * passes <"another" = true> parameter, clear the member's stripe_id for this session
         * to force rendering the approve-new view
         */
        if (array_key_exists('another', Yii::$app->request->get())) {
            $member->stripe_id = null;
        }

        $messages = $member->checkProfile();

        if (empty($messages)) {
            /* @var DuesRate $rate */
            $rate = DuesRate::findCurrentByTrade($member->currentStatus->lob_cd, $member->currentClass->rate_class);
            $stripe = $this->getStripeClient($member->currentStatus->lob_cd);
            $price = $stripe->prices->retrieve($rate->duesStripeProduct->stripe_price_id, ['expand' => ['product']]);

            if (isset($member->stripe_id)) {
                $customer = $stripe->customers->retrieve($member->stripe_id, ['expand' => ['default_source']]);
                if (isset($customer->default_source)) {
                    $card = $customer->default_source;
                    return $this->renderAjax('enroll-existing', [
                        'price' => $price,
                        'member' => $member,
                        'logo_path' => Yii::getAlias('@webroot') . Yii::$app->params['logoDir'],
                        'customer' => $customer,
                        'expired' => $this->cardExpired($card->exp_month, $card->exp_year),
                    ]);
                }
            }

            return $this->renderAjax('enroll-new', [
                'member' => $member,
                'price' => $price,
            ]);
        }

        $this->exceptionHandler('CCC020', 'Incomplete member profile', $messages);
        return $this->goBack();

    }

    /**
     * @return Response
     * @throws \Exception
     * @throws Throwable
     */
    public function actionAddSubscription()
    {
        $member = Member::findOne(filter_var_array(Yii::$app->request->post(),  ['member_id' => FILTER_SANITIZE_STRING]));
        $manager = new StripePaymentManagerSubscription(['member' => $member]);

        if ($manager->activeSubscriptionExists()) {
            $this->exceptionHandler('CCC021', 'An active subswcription exists for this member', ['Uncanceled subscription for: ' . $member->member_id]);
            return $this->goBack();
        }

        // Remove reference to (assumed) canceled subscription
        if (isset($member->subscription))
            $member->subscription->delete();


        $manager->attributes = Yii::$app->request->post();

        if (!($manager->validate())) {
            foreach ($manager->errors as $field => $messages)
                $this->exceptionHandler('CCC022', $field, $messages);
            return $this->goBack();
        }

        if (isset($manager->stripe_token)) {
            if (!($manager->processCard())) {
                foreach ($manager->messages as $code => $message)
                    $this->exceptionHandler($code, $message['friendly'], $message['system']);
                return $this->goBack();
            }
        }

        if (($subscription = $manager->createSubscription()) == false) {
            foreach ($manager->messages as $code => $message)
                $this->exceptionHandler($code, $message['friendly'], $message['system']);
            return $this->goBack();
        }

        $member_subs = new Subscription([
            'member_id' => $member->member_id,
            'stripe_id' => $subscription->id,
        ]);
        $member_subs->save();

        Yii::$app->session->addFlash('success', "Auto-pay subscription successfully added");

        return $this->goBack();

    }

    /**
     * Cancels Stripe subscription
     *
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionCancelSubscription($id)
    {
        $member = $this->findMember($id);
        if (!isset($member->subscription))
            throw new NotFoundHttpException('The requested page does not exist.');
        $stripe = $this->getStripeClient($member->currentStatus->lob_cd);

        try {
            $stripe_subs = $stripe->subscriptions->retrieve($member->subscription->stripe_id, []);

            if($stripe_subs->status <> Subscription::STATUS_CANCELED) {
                $stripe->subscriptions->cancel($member->subscription->stripe_id, []);
                Yii::$app->session->addFlash('success', "Auto-pay subscription successfully cancelled");
            } else
                Yii::$app->session->addFlash('notice', "No action. Subscription already cancelled");

        } catch (ApiErrorException $e) {
            $this->exceptionHandler('CCC060', 'Unable to cancel subscription', [$e->getMessage()]);
        } catch (Throwable $e) {
            $this->exceptionHandler('CCC061', 'Unable remove subscription reference', [$e->getMessage()]);
        }

        return $this->goBack();

    }

    /**
     * Processes credit card payment and builds an unposted receipt
     *
     * @return Response
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionPayment()
    {
        $member = Member::findOne(filter_var_array(Yii::$app->request->post(),  ['member_id' => FILTER_SANITIZE_STRING]));
        $manager = new StripePaymentManagerCharge(['member' => $member]);

        $manager->attributes = Yii::$app->request->post();

        if (!($manager->validate())) {
            foreach ($manager->errors as $field => $messages)
                $this->exceptionHandler('CCC025', $field, $messages);
            return $this->goBack();
        }

        if (isset($manager->stripe_token)) {
            if (!($manager->processCard())) {
                foreach ($manager->messages as $code => $message)
                    $this->exceptionHandler($code, $message['friendly'], $message['system']);
                return $this->goBack();
            }
        }

        $tracking = StripeTransaction::getTracking();
        if (($charge = $manager->createCharge($tracking)) == false) {
            foreach ($manager->messages as $code => $message) {
                $show_friendly = key_exists('show_friendly', $message);
                $this->exceptionHandler($code, $message['friendly'], $message['system'], $show_friendly);
            }
            return $this->goBack();
        }

        $receipt = new ReceiptMember([
            'scenario' => Receipt::SCENARIO_CREATE,
            'received_dt' => date("Y-m-d", $charge->created),
        ]);
        if (($receipt_id = $receipt->makeUnposted($member, $charge, filter_var_array(Yii::$app->request->post(),  ['other_local' => FILTER_SANITIZE_STRING]))) != false) {
            $transaction = new StripeTransaction([
                'transaction_id' => $charge->id,
                'trans_type' => StripeTransaction::TYPE_MANUAL,
                'customer_id' => $member->stripe_id,
                'tracking_nbr' => $tracking,
                'receipt_id' => $receipt_id,
            ]);
            $transaction->save();
        }

        return $this->redirect(['/receipt-member/update', 'id' => $receipt_id]);

    }

    /**
     * @param $id
     * @return string|Response|array
     * @throws ApiErrorException|NotFoundHttpException
     */
    public function actionUpdateCard($id)
    {
        if (!Yii::$app->user->can('createReceipt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));

        $model = new CreditCardUpdateForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($model);
        }

        $member = $this->findMember($id);

        if ($model->load(Yii::$app->request->post())) {
            $manager = new StripePaymentManager([
                'member' => $member,
            ]);
            if ($manager->updateCard($model) == true)
                Yii::$app->session->addFlash('success', "Credit card expire date successfully updated");
            return $this->goBack();
        }

        $stripe = $this->getStripeClient($member->currentStatus->lob_cd);
        $customer = $stripe->customers->retrieve($member->stripe_id, ['expand' => ['default_source']]);
        $card = $customer->default_source;
        $model->cardholder = $customer->name;
        $model->card_id = $card->id;
        $model->month = $card->exp_month;
        $model->year = $card->exp_year;
        return $this->renderAjax('update-card', [
            'model' => $model,
            'card' => $card,
        ]);

    }

    /**
     * @return Response
     */
    public function actionHandleWebhook1791()
    {
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $payload = Yii::$app->request->getRawBody();
        $stripe = $this->getStripeClient(Lob::TRADE_PT);
        $manager = new StripeEndpointManager(['stripe' => $stripe]);
        return $manager->handleEvent(Lob::TRADE_PT, $signature, $payload);
    }

    /**
     * @return Response
     */
    public function actionHandleWebhook1889()
    {
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $payload = Yii::$app->request->getRawBody();
        $stripe = $this->getStripeClient(Lob::TRADE_GL);
        $manager = new StripeEndpointManager(['stripe' => $stripe]);
        return $manager->handleEvent(Lob::TRADE_GL, $signature, $payload);
    }

    /**
     * @return Response
     */
    public function actionHandleWebhook1926()
    {
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $payload = Yii::$app->request->getRawBody();
        $stripe = $this->getStripeClient(Lob::TRADE_FL);
        $manager = new StripeEndpointManager(['stripe' => $stripe]);
        return $manager->handleEvent(Lob::TRADE_FL, $signature, $payload);
    }

    /**
     * @return Response
     */
    public function actionHandleWebhook1944()
    {
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $payload = Yii::$app->request->getRawBody();
        $stripe = $this->getStripeClient(Lob::TRADE_TP);
        $manager = new StripeEndpointManager(['stripe' => $stripe]);
        return $manager->handleEvent(Lob::TRADE_TP, $signature, $payload);
    }

    /**
     * @param $id
     * @return Member|null
     * @throws NotFoundHttpException
     */
    protected function findMember($id)
    {
        if (($member = Member::findOne($id)) == null) {
            Yii::error("*** MSC050: Could not find member `$id`");
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $member;
    }

    /**
     * @param $lob_cd
     * @return StripeClient
     */
    protected function getStripeClient($lob_cd)
    {
        return new StripeClient(Yii::$app->params['stripe'][$lob_cd]['secret_key']);
    }

    /**
     * In production, uses standard exception helper.  Should be overridden in unit tests
     *
     * @param $code
     * @param $message
     * @param array $errors
     * @param bool $show_friendly
     */
    protected function exceptionHandler($code, $message, array $errors, $show_friendly = false)
    {
        ExceptionHelper::handleError(Yii::$app->session, $code, $message, $errors, $show_friendly);
    }

    protected function cardExpired($exp_month, $exp_year)
    {
        $current = (new OpDate())->getYearMonth();
        return $current > $exp_year . $exp_month;
    }

}