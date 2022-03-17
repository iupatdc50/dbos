<?php

use app\models\member\Member;
use Stripe\Customer;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $member Member */
/* @var $price Stripe\Price */
/* @var $customer Customer */

?>

<div class="enroll-form">

    <form id="payment-form" method="post" action="<?= Url::to('/credit-card/add-subscription') ?>" novalidate>

        <?= $this->render('_plan', ['price' => $price])?>

        <?= $this->render('_existingcard', [
            'customer' => $customer,
            'member_id' => $member->member_id,
            'action' => 'enroll',
        ])?>


        <hr>

        <div class="row">
            <div class="col-xs-2 form-group">
                <label class="control-label" for="defer">Months to Defer</label>
                <input id="defer" name="defer" type="number" value="0" class="form-control required number" min="0" max="12">
                <p class="help-block help-block-error"></p>
            </div>
        </div>

        <br />

        <?= $this->render('_ccactions', ['submit_lbl' => 'Process Subscription']); ?>

    </form>

</div>


