<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

?>

<div class="registration-form">

    <?php $form = ActiveForm::begin([
    		'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'registration-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $this->render('_formfields', ['form' => $form, 'model' => $model]); ?>
        
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

