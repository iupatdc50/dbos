<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\InitFee */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="init-fee-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'lob_cd')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'member_class')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'effective_dt')->textInput() ?>

    <?= $form->field($model, 'end_dt')->textInput() ?>

    <?= $form->field($model, 'fee')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dues_months')->textInput() ?>

    <?= $form->field($model, 'included')->dropDownList([ 'T' => 'T', 'F' => 'F', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
