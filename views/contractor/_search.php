<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\ContractorSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contractor-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'license_nbr') ?>

    <?= $form->field($model, 'contractor') ?>

    <?= $form->field($model, 'contact_nm') ?>

    <?= $form->field($model, 'email') ?>

    <?= $form->field($model, 'url') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
