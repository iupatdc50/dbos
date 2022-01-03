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

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['price_id', 'required'],
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
        if ($date->getDay() > self::ANCHOR_DAY)
            $date->modify('+1 month');
        $anchor = gmmktime(0, 0, 0, $date->getMonth(), 15, $date->getYear());

        try {
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $this->member->stripe_id,
                'items' => [
                    ['price' => $this->price_id],
                ],
                'billing_cycle_anchor' => $anchor,
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

}