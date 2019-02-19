<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\DetailView;
use kartik\form\ActiveForm;

/* @var $model app\models\member\RepairDuesForm */
/* @var $member app\models\member\Member */

// The controller action that will render the list
$url = Url::to(['/member-balances/alloc-list']);

?>

<div class="repair-form">

<table class="hundred-pct"><tr><td class="fifty-pct datatop">
    <?=
    /** @noinspection PhpUnhandledExceptionInspection */
    DetailView::widget([
            'model' => $member,
            'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table-sm text-left'],
            'attributes' => [
                'fullName',
                [
                    'attribute' => 'dues_paid_thru_dt',
                    'format' => 'date',
                    'label' => 'Current Dues Thru',
                ],
                [
                    'attribute' => 'overage',
                    'label' => 'Current Overage',
                ],
            ],
        ]);
    ?>
        </td><td></td><td class="thirtyfive-pct">
    <?php $form = ActiveForm::begin([
//        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => 'repair-form',
        'enableAjaxValidation' => true,
    ]); ?>

            <?= $form->field($model, 'alloc_id')->widget(Select2::classname(), [
                'size' => Select2::SMALL,
                'options' => ['placeholder' => 'Search...', ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'id' => 'alloc_id',
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => $url,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {search:params.term,member_id:"' . $member-> member_id . '"}; }'),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(alloc) { return alloc.text; }'),
                    'templateSelection' => new JsExpression('function(alloc) { return alloc.text; }'),
                ],
                'pluginEvents' => [
                    'select2:select' => "function(e) {  
                        \$.get('/member-balances/get-base', { alloc_id : e.params.data.id }, function(data) {
                            \$('#paidthru').val(data.paid_thru_dt);
                            \$('#overage').val(data.overage);
                        });
                    }",
                ],
            ])->label('Last Good Receipt'); ?>

            <?= $form->field($model, 'paid_thru_dt')->hiddenInput(['id' => 'paidthru'])->label(false);  ?>
            <?= $form->field($model, 'overage')->textInput(['maxlength' => true, 'id' => 'overage'])  ?>

    <div class="form-group">
        <?= Html::submitButton('Repair', ['class' => 'btn btn-success']) ?>
    </div>
        <br />
            <div class="flash-notice">*** WARNING ***<br />This action cannot be undone!</div>

    <?php ActiveForm::end(); ?>
        </td></tr>
</table>

</div>

