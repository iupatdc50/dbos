<?php

use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $email app\models\contractor\Email */
/* @var $form kartik\form\ActiveForm */
?>

<div class="phone-fields">

    <?= $form->field($email, 'email_type')->widget(Select2::className(), [
    		'data' => $email->typeOptions,
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Email Type...'],
    ]) ?>
 
    <?= $form->field($email, 'email')->textInput(['maxlength' => true]) ?>


</div>
