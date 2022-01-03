<?php

use Stripe\Customer;
use app\models\member\Member;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $member Member */
/* @var $has_ccg boolean */
/* @var $total_due float */
/* @var $customer Customer */

?>

<div class="payment-form">

    <form id="payment-form" method="post" action = "<?= Url::to('/credit-card/payment') ?>" novalidate>
        <?= $this->render('_charge', [
                'total_due' => $total_due,
                'has_ccg' => $has_ccg,
            ])?>

        <hr>

        <?= $this->render('_existingcard', [
                'customer' => $customer,
                'member_id' => $member->member_id,
                'action' => 'approve',
        ])?>

        <hr>

        <?= $this->render('_ccactions', ['submit_lbl' => 'Submit Payment']); ?>

    </form>

</div>

<?php

$script = <<< JS

$(function() {
    formValidation();
});

JS;
$this->registerJs($script);
?>
