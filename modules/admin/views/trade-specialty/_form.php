<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\value\TradeSpecialty */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="trade-specialty-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'lob_cd')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'specialty')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
