<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ZipCode */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="zip-code-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'zip_cd')->textInput(['maxlength' => 5]) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'island')->textInput(['maxlength' => 15]) ?>

    <?= $form->field($model, 'st')->textInput(['maxlength' => 2]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
