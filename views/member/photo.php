<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\FileInput;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="photo-form">

    <?php $form = ActiveForm::begin([
    		'id' => 'memberPhoto-form', 
    		'enableClientValidation' => true,
    		'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $form->field($model, 'photo_file')->widget(FileInput::className(), [
    		'options' => ['accept' => 'image/*']]);    ?>
    
    <?php ActiveForm::end(); ?>

</div>
