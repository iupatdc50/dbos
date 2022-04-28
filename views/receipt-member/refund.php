<?php

use app\helpers\OptionHelper;
use app\models\accounting\ReceiptMember;
use app\models\accounting\RefundForm;
use Stripe\Card;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model RefundForm */
/* @var $receipt ReceiptMember */
/* @var $card Card */

?>

<div class="refund-view">
    <div class="flash-notice">A refund will be applied as indicated below.  The receipt will be voided.  <span class="emphasize">NOTE: This process cannot be reversed once submitted.</span></div>
    <?= /** @noinspection PhpUnhandledExceptionInspection */
    DetailView::widget([
        'model' => $receipt,
        'options' => ['class' => 'table table-cc-existing detail-view ninety-pct'],
        'attributes' => [
            'id',
            'payor_nm',
            'received_amt',
            [
                    'label' => 'Refund to',
                    'value' => Html::img(Yii::$app->params['logoDir'] . OptionHelper::getBrandLogoNm($card->brand), ['class' => 'cc-logo']) . ' ' . $card->brand . ': ' . OptionHelper::getBrandMask($card->brand) . $card->last4 ,
                    'format' => 'raw',
            ],
        ],
    ]); ?>
</div>

<div class="refund-form">
    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'id' => 'refund-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
    ]); ?>

    <?= $form->field($model, 'receipt_id')->hiddenInput()->label(false); ?>
    <?= $form->field($model, 'charge_id')->hiddenInput()->label(false); ?>

    <hr>

    <div class="form-group">
        <?= Html::submitButton('Submit Refund', [
            'class' => 'btn btn-success',
            'id' => 'submitter',
//            'disabled' => true,
        ]); ?>
        <?= Html::button('Cancel', [
            'class' => 'btn btn-default',
            'data-dismiss' => 'modal',
        ]); ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>



