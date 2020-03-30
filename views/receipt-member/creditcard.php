<?php

use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $payment_data array */

?>

<div class="payment-form">

    <form id="payment-form" action="<?= Url::to('/receipt-member/payment') ?>" method="post" novalidate>
        <div class="row">
            <div class="col-xs-2">
                <label for="currency">Currency</label>
                <input id="currency" name="currency" class="form-control" value="<?= $payment_data['currency'] ?>" readonly>
            </div>
            <div class="col-xs-10 form-group">
                <label class="control-label" for="charge">Charge</label>
                <input id="charge" name="charge" class="form-control required number" type="number" value="<?= number_format($payment_data['charge'], 2) ?>">
            </div>
        </div>
        <br />
        <div class="form-group">
            <label class="control-label" for="email">Receipt Email</label>
            <input id="email" name="email" type="email" value="<?= $payment_data['email'] ?>" class="form-control required email" placeholder="Receipt email">
        </div>
        <?php if ($payment_data['has_ccg'] == true): ?>
            <br />
            <div class="form-group">
                <label for="other_local">Receiving Local</label>
                <input id="other_local" name="other_local" class="form-control field-room digits">
            </div>
        <?php endif; ?>
        <hr>
        <div class="form-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input id="cardholder_nm" name="cardholder_nm" class="form-control required" placeholder="Name on card" required>
            <p class="help-block help-block-error"></p>
        </div>
        <br />

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

$(document).ready(function() {
    configStripe('$pubkey');
    $("#payment-form").validate({
        errorClass: "help-block",
        errorElement: "p",
        highlight: function ( element ) {
            $( element ).parents( ".form-group" ).addClass( "has-error" ).removeClass( "has-success" );
        },
        unhighlight: function (element) {
            $( element ).parents( ".form-group" ).addClass( "has-success" ).removeClass( "has-error" );
        }
    });
});


JS;
$this->registerJs($script);
?>
