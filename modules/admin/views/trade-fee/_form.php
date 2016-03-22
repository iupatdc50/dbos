<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\TradeFee */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="trade-fee-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'lob_cd')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fee_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'employer_remittable')->dropDownList([ 'T' => 'T', 'F' => 'F', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'member_remittable')->dropDownList([ 'T' => 'T', 'F' => 'F', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
