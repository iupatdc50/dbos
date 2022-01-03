<?php

use app\helpers\OptionHelper;
use yii\bootstrap\Html;
use Stripe\Customer;
use Stripe\Card;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $customer Customer */
/* @var $member_id string */
/* @var $action string */

/* @var Card $card */
$card = $customer->default_source;

?>

<div id="existing-card">
    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken; ?>">
    <input type="hidden" name="member_id" value="<?= $member_id; ?>">
    <input type="hidden" name="card_id" value="<?= $card->id; ?>">
    <div>
        <label for="cardholder">Cardholder</label>
        <input id="cardholder" name="cardholder" class="form-control" value="<?= $customer->name; ?>" readonly>
    </div>
    <br />
    <table class="table table-cc-existing ninety-pct">
        <tr><th>Payment Method</th><th>Expires</th><th></th></tr>
        <tr>
            <td class="td-middle">
                <?= Html::img(Yii::$app->params['logoDir'] . OptionHelper::getBrandLogoNm($card->brand), ['class' => 'cc-logo']) . ' ' . $card->brand . ': ' . OptionHelper::getBrandMask($card->brand) . $card->last4 ?>
            </td>
            <td class="td-middle">
                <?= $card->exp_month . '/' . $card->exp_year ?>
            </td>
            <td>
                <?=
                    Html::button('<i class="glyphicon glyphicon-credit-card"></i> Use another card', [
                        'class' => 'btn btn-default btn-modal',
                        'id' => 'anotherCardButton',
                        'name' => 'another-card',
                        'value' => Url::to(["credit-card/$action", 'id'  => $member_id, 'another' => true]),
                        'data-title' => 'Another Credit Card',
                        'title' => 'Enter another credit card',
                    ]);
                ?>
            </td>
        </tr>
    </table>
</div>

<?php

