<?php

use app\helpers\OptionHelper;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\CreditCardUpdateForm */
/* @var $card Stripe\Card */

?>
<div id="update">

    <?php $form = ActiveForm::begin([
//        'layout' => 'horizontal',
        'id' => 'employ-form',
        'enableClientValidation' => true,
    ]); ?>

    <table class="table table-cc-existing ninety-pct">
        <?= $form->field($model, 'cardholder')->textInput(['maxlength' => true, 'readonly' => true]) ?>
        <tr><th>Payment Method</th><th>Expires</th></tr>
        <tr>
            <td class="sixty-pct td-middle">
                <?= Html::img('../img/' . OptionHelper::getBrandLogoNm($card->brand), ['class' => 'cc-logo']) . ' ' . $card->brand . ': ' . OptionHelper::getBrandMask($card->brand) . $card->last4 ?>
            </td>
            <td>
                <table><tr>
                    <td class="td-middle pad-six">
                        <?= $form->field($model, 'month')->textInput(['maxlength' => 2])->label(false) ?>
                    </td>
                    <td class="td-middle">/</td>
                    <td class="td-middle pad-six">
                        <?= $form->field($model, 'year')->textInput(['maxlength' => 4])->label(false) ?>
                    </td>
                </tr></table>
            </td>
        </tr>
    </table>

    <?= $form->field($model, 'card_id')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Update', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>



