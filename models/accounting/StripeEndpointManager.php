<?php

namespace app\models\accounting;

use app\helpers\OptionHelper;
use app\models\member\Member;
use app\models\member\SubscriptionEvent;
use Exception;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Invoice;
use Stripe\StripeClient;
use Stripe\Subscription;
use Stripe\Webhook;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\web\Response;

class StripeEndpointManager extends Model
{

    /* @var StripeClient $stripe Must be injected */
    public $stripe;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if(!(isset($this->stripe) && ($this->stripe instanceof StripeClient)))
            throw new InvalidConfigException('No StripeClient object injected');
    }

    /**
     * @param $lob_cd
     * @param $signature
     * @param $payload
     * @return Response
     */
    public function handleEvent($lob_cd, $signature, $payload)
    {

        $wh_secret = Yii::$app->params['stripe'][$lob_cd]['wh_secret'];
        try {
            $event = Webhook::constructEvent($payload, $signature, $wh_secret);
        } catch(UnexpectedValueException $e) {
            $msg = 'Invalid payload';
            Yii::error('⚠️  ' . $msg . ': ' . $e->getMessage());
            Yii::info('Payload: ' . $payload);
            return $this->status400($msg);
        } catch(SignatureVerificationException $e) {
            $msg =  'Invalid signature';
            Yii::error('⚠️  ' . $msg . '. Payload ignored: ' . $e->getMessage());
            return $this->status400($msg);
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $object = $event->data->object;

        if ($object instanceof Subscription) {
            if (isset($object->canceled_at) && isset($object->customer)) {
                // Handle canceled event
                $member = Member::findOne(['stripe_id' => $object->customer->id]);
                $member->subscription->is_active = OptionHelper::TF_FALSE;
                $member->subscription->save();
            } // Ignore non-cancelled Subscription

        } elseif ($object instanceof Invoice) {

            if (isset($object->subscription)) {

                // Null charge invoices are generated for "trial period" subscriptions
                if (isset($object->charge)) {

                    // Handle the Invoice event
                    try {
                        $charge = $this->stripe->charges->retrieve($object->charge, ['expand' => ['customer']]);
                    } catch (ApiErrorException $e) {
                        $msg = 'Problem accessing charge on object';
                        Yii::error('⚠️  ' . $msg . ': ' . $e->getMessage());
                        Yii::info('Payload: ' . $payload);
                        return $this->status400($msg);
                    }
                    $member = Member::findOne(['stripe_id' => $charge->customer->id]);
                    if (isset($member)) {
                        $eventLog = new SubscriptionEvent([
                            'event_id' => $event->id,
                            'customer_id' => $charge->customer->id,
                            'invoice_id' => $object->number,
                            'created_dt' => date("Y-m-d", $charge->created),
                            'charge_amt' => $charge->amount / 100,
                        ]);

                        switch ($event->type) {
                            case 'invoice.paid':
                                $receipt = new ReceiptMember([
                                    'scenario' => Receipt::SCENARIO_CREATE,
                                    'received_dt' => date("Y-m-d", $charge->created),
                                ]);
                                try {
                                    $receipt_id = $receipt->makePosted($member, $charge, $object->number);
                                } catch (Exception $e) {
                                    Yii::error('⚠️  Could not post receipt. Error: ' . $e->getMessage());
                                    Yii::info('Payload: ' . $payload);
                                    break;
                                }
                                if ($receipt_id == false)
                                    break;
                                $transaction = new StripeTransaction([
                                    'transaction_id' => $charge->id,
                                    'trans_type' => StripeTransaction::TYPE_AUTO,
                                    'customer_id' => $charge->customer->id,
                                    'tracking_nbr' => $object->number,
                                    'receipt_id' => $receipt_id,
                                ]);
                                $transaction->save();
                                $eventLog->status = SubscriptionEvent::STATUS_PAID;
                                $eventLog->receipt_id = $receipt_id;
                                break;
                            case 'invoice.payment_failed':
                                $eventLog->status = SubscriptionEvent::STATUS_FAILED;
                                if (isset($object->next_payment_attempt))
                                    $eventLog->next_attempt = $object->next_payment_attempt;
                                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                                $eventLog->reason = $charge->outcome->reason;
                                break;
                            default:
                                // Unexpected event type
                                Yii::error(print_r('*** Received unknown event type: ' . $event, true));
                        }

                        $eventLog->save();

                    } else // Member find is unsuccessful
                        Yii::error('⚠️  Stripe customer does not match any member. Payload ignored: ' . $payload);
                } // Ignore null charge invoices

            } // Ignore non-Subscription invoices
        }

        return new Response([
            'format' => Response::FORMAT_JSON,
            'statusCode' => 200,
            'statusText' => 'Webhook Handled',
        ]);

    }

    protected function status400($msg)
    {
        return new Response([
            'format' => Response::FORMAT_JSON,
            'statusCode' => 400,
            'statusText' => $msg,
        ]);
    }

}