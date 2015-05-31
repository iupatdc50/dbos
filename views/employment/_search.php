<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\member\EmploymentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="employment-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'member_id') ?>

    <?= $form->field($model, 'effective_dt') ?>

    <?= $form->field($model, 'end_dt') ?>

    <?= $form->field($model, 'employer') ?>

    <?= $form->field($model, 'dues_payor') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
