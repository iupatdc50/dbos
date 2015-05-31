<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $phone app\models\base\BasePhone */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="phone-fields">

    <?= $form->field($phone, 'phone_type')->widget(Select2::className(), [
    		'data' => $phone->typeOptions,
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Phone Type...'],
    ]) ?>
 
    <?= $form->field($phone, 'phone')->textInput(['maxlength' => 14]) ?>

    <?= $form->field($phone, 'ext')->textInput(['maxlength' => 7]) ?>


</div>
