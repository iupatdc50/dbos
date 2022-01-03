<?php

use app\models\member\Member;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $member Member */
/* @var $has_ccg boolean */
/* @var $total_due float */


?>

<div class="payment-form">

    <form name="payment-form" id="payment-form" method="post" action = "<?= Url::to('/credit-card/payment') ?>" novalidate>
        <?= $this->render('_charge', [
                'total_due' => $total_due,
                'has_ccg' => $has_ccg,
            ])?>

        <hr>

        <?= $this->render('_newcard', ['member' => $member])?>

        <hr>

        <?= $this->render('_ccactions', ['submit_lbl' => 'Submit Payment']); ?>

    </form>

</div>

<?php
$pubkey = Yii::$app->params['stripe'][$member->currentStatus->lob_cd]['publishable_key'];

$script = <<< JS

$(function() {
    configStripe('$pubkey');
    formValidation();
});

JS;
$this->registerJs($script);
?>
