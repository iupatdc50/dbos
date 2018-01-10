<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\training\CredentialSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="credential-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'credential') ?>

    <?= $form->field($model, 'display_seq') ?>

    <?= $form->field($model, 'card_descrip') ?>

    <?= $form->field($model, 'catg') ?>

    <?php // echo $form->field($model, 'duration') ?>

    <?php // echo $form->field($model, 'show_on_cert') ?>

    <?php // echo $form->field($model, 'show_on_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
