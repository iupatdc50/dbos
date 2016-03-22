<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\Receipt */
/* @var $form yii\widgets\ActiveForm */

// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);

?>

<div class="receipt-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    ]); ?>
    
    

    <?= $form->field($model, 'payment_method')->dropDownList([ 1 => '1', 2 => '2', 3 => '3', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'payor_type')->dropDownList([ 'C' => 'C', 'M' => 'M', 'O' => 'O', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'received_dt')->textInput() ?>

    <?= $form->field($model, 'received_amt')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unallocated_amt')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
