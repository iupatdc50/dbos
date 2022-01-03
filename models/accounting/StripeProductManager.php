<?php

namespace app\models\accounting;

use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\StripeClient;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

class StripeProductManager extends Model
{
    private $_lob_cd;
    /* @var DuesRate $rate Must be injected on instantiate */
    public $rate;
    public $messages = [];

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        // Must inject a DuesRate with a current status entry.
        if(!(isset($this->rate) && ($this->rate instanceof DuesRate)))
            throw new InvalidConfigException('No rate object injected');
        $this->_lob_cd = $this->rate->lob_cd;
    }

    /**
     * Creates Stripe product for subscriptions
     *
     * @return false|Price
     */
    public function createProduct()
    {
        $stripe = $this->getStripeClient();
        try {
            $product = $stripe->products->create([
                'name' => $this->rate->rateClass->descrip,
            ]);
            $price = $stripe->prices->create([
                'unit_amount' => intval($this->rate->rate * 100),
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'product' => $product->id,
            ]);
        } catch (ApiErrorException $e) {
            $this->messages['SPM060'] = [
                'friendly' => 'Internal error: ',
                'system' => [$e->getError()->message],
            ];
            return false;
        }
        return $price;
    }

    protected function getStripeClient()
    {
        return new StripeClient(Yii::$app->params['stripe'][$this->_lob_cd]['secret_key']);
    }

}