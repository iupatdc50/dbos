<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\member\MemberClass */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="member-class-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'member_id')->textInput(['maxlength' => 11]) ?>

    <?= $form->field($model, 'effective_dt')->textInput() ?>

    <?= $form->field($model, 'end_dt')->textInput() ?>

    <?= $form->field($model, 'member_class')->textInput(['maxlength' => 1]) ?>

    <?= $form->field($model, 'rate_class')->textInput(['maxlength' => 2]) ?>

    <?= $form->field($model, 'wage_percent')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
