<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\member\Status */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="status-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'member_id')->textInput(['maxlength' => 11]) ?>

    <?= $form->field($model, 'effective_dt')->textInput() ?>

    <?= $form->field($model, 'end_dt')->textInput() ?>

    <?= $form->field($model, 'lob_cd')->textInput(['maxlength' => 4]) ?>

    <?= $form->field($model, 'member_status')->textInput(['maxlength' => 1]) ?>

    <?= $form->field($model, 'reason')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
