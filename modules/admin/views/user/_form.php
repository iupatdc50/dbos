<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\user\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-record-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'last_nm')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'first_nm')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'password_clear')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>

    <?php
        if($model->isNewRecord)
            echo $form->field($model, 'role')->hiddenInput()->label(false);
        else
            echo $form->field($model, 'role')->textInput(['maxlength' => true]);
    ?>

    <?= $form->field($model, 'status')->widget(Select2::className(), [
    		'data' => $model->statusOptions, 
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Status...'],
    ]) ?>
    
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
