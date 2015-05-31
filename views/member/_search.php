<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\member\MemberSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="member-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'member_id') ?>

    <?= $form->field($model, 'ssnumber') ?>

    <?= $form->field($model, 'report_id') ?>

    <?= $form->field($model, 'last_nm') ?>

    <?= $form->field($model, 'first_nm') ?>

    <?php // echo $form->field($model, 'middle_inits') ?>

    <?php // echo $form->field($model, 'suffix') ?>

    <?php // echo $form->field($model, 'birth_dt') ?>

    <?php // echo $form->field($model, 'gender') ?>

    <?php // echo $form->field($model, 'shirt_size') ?>

    <?php // echo $form->field($model, 'local_pac') ?>

    <?php // echo $form->field($model, 'hq_pac') ?>

    <?php // echo $form->field($model, 'remarks') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
