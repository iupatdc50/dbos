<?php

use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $payment_data array */

?>

<div class="payment-form">

    <form id="payment-form" action="<?= Url::to('/receipt-member/payment') ?>" method="post">
        <div class="row">
            <div class="col-xs-2">
                <label for="currency">Currency</label>
                <input id="currency" name="currency" class="form-control field-room" value="<?= $payment_data['currency'] ?>" readonly>
            </div>
            <div class="col-xs-10">
                <label for="charge">Charge</label>
                <input id="charge" name="charge" class="form-control field-room" value="<?= number_format($payment_data['charge'], 2) ?>">
            </div>
        </div>
        <div>
            <label for="email">Receipt Email</label>
            <input id="email" name="email" value="<?= $payment_data['email'] ?>" class="form-control field-room" placeholder="Receipt email">
        </div>
        <?php if ($payment_data['has_ccg'] == true): ?>
            <div>
                <label for="other_local">Receiving Local</label>
                <input id="other_local" name="other_local" class="form-control field-room">
            </div>
        <?php endif; ?>
        <hr>
        <div>
            <!--suppress HtmlFormInputWithoutLabel -->
            <input id="cardholder_nm" name="cardholder_nm" class="form-control field-room" placeholder="Name on card" required>
        </div>

        <input type="hidden" name="member_id" value="<?= $payment_data['member_id'] ?>">
        <input type="hidden" name="lob_cd" value="<?= $payment_data['lob_cd'] ?>">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken; ?>">

        <div id="card-element" class="form-control">
            <!-- Elements will create input elements here -->
        </div>

        <!-- We'll put the error messages in this element -->
        <div id="card-errors" role="alert" class="help-block help-block-error"></div>
        <hr>

        <div class="form-group">
            <?= Html::submitButton('Submit Payment', [
                'class' => 'btn btn-success',
                'id' => 'submitter',
            ]) ?>
        </div>
    </form>

</div>

<?php
$pubkey = Yii::$app->params['stripe'][$payment_data['lob_cd']]['publishable_key'];

$script = <<< JS

configStripe('$pubkey');


JS;
$this->registerJs($script);
?>
