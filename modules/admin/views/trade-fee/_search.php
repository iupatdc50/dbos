<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\TradeFeeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="trade-fee-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'lob_cd') ?>

    <?= $form->field($model, 'fee_type') ?>

    <?= $form->field($model, 'employer_remittable') ?>

    <?= $form->field($model, 'member_remittable') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
