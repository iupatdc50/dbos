<?php

namespace app\models\accounting;

use Stripe\Charge;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;

class StripePaymentManagerCharge extends StripePaymentManager
{

    /**
     * Charges customer in Stripe
     *
     * @param $tracking int Caller supplied tracking number
     * @return false|Charge|null
     */
    public function createCharge($tracking)
    {
        if (!isset($this->member->stripe_id)) {
            $this->messages['SPM040'] = [
                'friendly' => 'Member does not have a Stripe customer ID',
                'system' => []];
            return false;

        }

        $charge = null;

        try {

            $customer = $this->stripe->customers->retrieve($this->member->stripe_id);
            $charge = $this->stripe->charges->create([
                'amount' => $this->charge * 100,
                'currency' => $this->currency,
                'description' => 'DC50 in-office payment',
                'receipt_email' => $customer->email,
                'customer' => $customer->id,
                'metadata' => [
                    'tracking' => $tracking,
                    'trade' => $this->member->currentStatus->lob_cd,
                    // Cardholder name is for receipt without having to retrieve Stripe customer again
                    'cardholder_nm' => $customer->name,
                ],
            ]);

        } catch (CardException $e) {
            $this->messages['SPM051'] = [
                'friendly' => 'Payment unsuccessful: ',
                'system' => [$e->getError()->message],
            ];
        } catch (RateLimitException $e) {
            $this->messages['SPM052'] = [
                'friendly' => 'You made too many requests made too quickly.  Please try again later.',
                'system' => [],
            ];
        } catch (InvalidRequestException $e) {
            $this->messages['SPM053'] = [
                'friendly' => 'Internal error: ',
                'system' => [$e->getError()->message],
            ];
        } catch (AuthenticationException $e) {
            $this->messages['SPM054'] = [
                'friendly' => 'Internal error: ',
                'system' => [$e->getError()->message],
            ];
        } catch (ApiConnectionException $e) {
            $this->messages['SPM055'] = [
                'friendly' => 'Connectivity problems or network interruption.  Please try again later.',
                'system' => [],
            ];
        } catch (ApiErrorException $e) {
            $this->messages['SPM056'] = [
                'friendly' => 'Internal error: ',
                'system' => [$e->getError()->message],
            ];
        }

        if (empty($this->messages))
            return $charge;

        return false;

    }

}