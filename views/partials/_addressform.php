<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\base\BaseAddress */
/* @var $form kartik\form\ActiveForm */
?>

<div class="address-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
       		'id' => 'address-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $this->render('_addressformfields', ['form' => $form, 'address' => $model]); ?>
        
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

