<?php

use kartik\datecontrol\DateControl;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\training\MemberCredential */
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
        'options' => ['placeholder' => 'Select...', 'id' => 'mclass'],
    ]) ?>

    <?= $form->field($model, 'complete_dt')->widget(DateControl::className(), [
        'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

