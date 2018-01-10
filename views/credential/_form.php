<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\training\Credential */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="credential-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'credential')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'display_seq')->textInput() ?>

    <?= $form->field($model, 'card_descrip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'catg')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'duration')->textInput() ?>

    <?= $form->field($model, 'show_on_cert')->dropDownList([ 'T' => 'T', 'F' => 'F', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'show_on_id')->dropDownList([ 'T' => 'T', 'F' => 'F', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
