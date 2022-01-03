<?php

namespace app\models\accounting;

use app\components\validators\AtLeastValidator;
use app\helpers\ExceptionHelper;
use app\models\member\Email;
use app\models\member\Member;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;

class StripePaymentManager extends Model
{
    /* @var StripeClient $stripe */
    protected $stripe;
    protected $lob_cd;

    /* @var Member $member Must be injected on instantiate */
    public $member;
    public $email;
    public $charge;
    public $other_local;
    public $cardholder_nm;
    public $currency = 'usd';
    public $stripe_token;
    public $card_id; // Exists if CC already exists in Stripe
    public $messages = [];

    public function rules()
    {
        return [
            ['email', 'email'],
            ['charge', 'number'],
            [['cardholder_nm', 'currency', 'other_local', 'stripe_token', 'card_id'], 'string'],
            ['stripe_token', AtLeastValidator::className(), 'in' => ['stripe_token', 'card_id']],
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        // Must inject a member with a current status entry.
        if(!(isset($this->member) && ($this->member instanceof Member)))
            throw new InvalidConfigException('No member object injected');
        if(!(isset($this->member->currentStatus)))
            throw new InvalidConfigException('Cannot determine member trade');
        $this->lob_cd =  $this->member->currentStatus->lob_cd;
        $this->stripe = new StripeClient(Yii::$app->params['stripe'][$this->lob_cd]['secret_key']);
    }

    /**
     * Handles the CC token
     *
     * If a Stripe Customer already exists, then the token is used to update the card reference (default source)
     * in the customer record.  Otherwise, a new Customer object is built and referenced in the DBOS member record
     *
     * @return bool
     * @throws Exception
     */
    public function processCard()
    {
        if (!isset($this->stripe_token)) {
            $this->messages['SPM030'] = [
                'friendly' => 'Did not return CC verification token',
                'system' => []];
            return false;
        }

        if (!isset($this->member->defaultEmail))
            $this->member->addEmail(new Email(['email' => $this->email]));

        try {
            if (isset($this->member->stripe_id)) {
                $this->stripe->customers->update($this->member->stripe_id, [
                    'source' => $this->stripe_token,
                ]);
            } else {
                $customer = $this->stripe->customers->create([
                    'email' => $this->email,
                    'name' => $this->cardholder_nm,
                    'source' => $this->stripe_token,
                ]);
                $this->member->stripe_id = $customer->id;
                $this->member->save();
            }
        } catch (ApiErrorException $e) {
            $this->messages[ExceptionHelper::CC_AUTH_ERROR] =  [
                'friendly' => 'Unable to process CC verification token',
                'system' => [$e->getMessage()]];
            return false;
        }

        return true;

    }

    public function updateCard(CreditCardUpdateForm $model)
    {
        try {
            $this->stripe->customers->updateSource($this->member->stripe_id, $model->card_id, [
                'exp_month' => $model->month,
                'exp_year' => $model->year,
            ]);
        } catch (ApiErrorException $e) {
            $this->messages['SPM060'] = [
                'friendly' => 'Internal error: ',
                'system' => [$e->getError()->message],
            ];
            return false;
        }
        return true;
    }

}

