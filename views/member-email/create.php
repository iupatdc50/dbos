<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\member\Email */
/* @var $form kartik\form\ActiveForm */
?>

<div class="email-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
       		'id' => 'email-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 50])->label('Email') ?>

    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>