<?php

namespace app\models\accounting;

use app\components\utilities\OpDate;
use Exception;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription;

class StripePaymentManagerSubscription extends StripePaymentManager
{
    // By policy, the bill date for each cycle is the 15th of the month
    CONST ANCHOR_DAY = 15;
    public $price_id;
    public $defer;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['price_id', 'defer'], 'required'],
        ]);
    }

    /**
     *
     * @return false|Subscription
     * @throws Exception
     */
    public function createSubscription()
    {
        if (!isset($this->member->stripe_id)) {
            $this->messages['SPM050'] = [
                'friendly' => 'Member does not have a Stripe customer ID',
                'system' => []];
            return false;
        }

        $date = new OpDate();
        $start_prop = 'billing_cycle_anchor';

        // If subscription is entered on the 15th, force 1 month trial (Stripe constraint)
        if (((int)$this->defer == 0) && ($date->getDay() == self::ANCHOR_DAY))
            $this->defer = '1';

        if (((int)$this->defer) > 0) {
            $date->modify("+$this->defer month");
            $start_prop = 'trial_end';
        }
        if ($date->getDay() > self::ANCHOR_DAY)
            $date->modify('+1 month');

        // Noon hour keeps the date timezone agnostic for US
        $anchor = gmmktime(12, 0, 0, $date->getMonth(), self::ANCHOR_DAY, $date->getYear());

        try {
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $this->member->stripe_id,
                'items' => [
                    ['price' => $this->price_id],
                ],
                $start_prop => $anchor,
                'proration_behavior' => 'none',
            ]);
        } catch (ApiErrorException $e) {
            $this->messages['SPM056'] = [
                'friendly' => 'Internal error: ',
                'system' => [$e->getError()->message],
            ];
            return false;
        }

        return $subscription;
    }

    /**
     * @return bool
     */
    public function activeSubscriptionExists()
    {
        $memb_subs = $this->member->subscription;
        if (isset($memb_subs)) {
            try {
                $stripe_subs = $this->stripe->subscriptions->retrieve($memb_subs->stripe_id, []);
            } catch (ApiErrorException $e) {
                $this->messages['SPM070'] = [
                    'friendly' => 'Internal error: ',
                    'system' => [$e->getError()->message],
                ];
                return false;
            }
            if (isset($stripe_subs) && ($stripe_subs->status <> Subscription::STATUS_CANCELED))
                return true;
        }
        return false;
    }

}