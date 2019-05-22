<?php

use kartik\datecontrol\DateControl;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\training\MemberCredential */
/* @var $modelResp app\models\training\MemberRespirator */
/* @var $form kartik\form\ActiveForm */
?>

<div class="credential-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
       		'id' => 'credential-form',
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'credential_id')->widget(Select2::className(), [
        'data' => $model->getCredentialOptions($model->catg),
        'hideSearch' => false,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Select...', 'id' => 'credId'],
    ]) ?>

    <?= $form->field($model, 'complete_dt')->widget(DateControl::className(), [
        'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <div id="respirator">
        <?= $form->field($modelResp, 'brand')->widget(Select2::className(), [
            'data' => $modelResp->getBrandOptions(),
            'hideSearch' => false,
            'size' => Select2::SMALL,
            'options' => ['placeholder' => 'Select...', 'id' => 'brand'],
        ]) ?>

        <?= $form->field($modelResp, 'resp_size')->widget(Select2::className(), [
            'data' => $modelResp->getSizeOptions(),
            'hideSearch' => true,
            'size' => Select2::SMALL,
            'options' => ['placeholder' => 'Select...', 'id' => 'size'],
        ]) ?>

        <?= $form->field($modelResp, 'resp_type')->widget(Select2::className(), [
            'data' => $modelResp->getTypeOptions(),
            'hideSearch' => true,
            'size' => Select2::SMALL,
            'options' => ['placeholder' => 'Select...', 'id' => 'type'],
        ]) ?>

    </div>

    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(function() {
    toggle($('#credId').val());
});

$('#credId').change(function() {
    toggle($(this).val());
});

function toggle(credId) {
    var respopt = $('#respirator');
    if(credId == 28) {
        respopt.show();
    } else {
        respopt.hide();
    }
}

JS;
$this->registerJs($script);
?>

