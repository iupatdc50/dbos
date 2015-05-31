<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\value\BillRate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bill-rate-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'lob_cd')->textInput(['maxlength' => 4]) ?>

    <?= $form->field($model, 'member_class')->textInput(['maxlength' => 2]) ?>

    <?= $form->field($model, 'rate_class')->textInput(['maxlength' => 2]) ?>

    <?= $form->field($model, 'fee_type')->textInput(['maxlength' => 2]) ?>

    <?= $form->field($model, 'effective_dt')->textInput() ?>

    <?= $form->field($model, 'end_dt')->textInput() ?>

    <?= $form->field($model, 'rate')->textInput(['maxlength' => 7]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
