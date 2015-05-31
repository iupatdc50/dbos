<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\base\BasePhone */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="phone-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
    		'id' => 'contractorPhone-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $this->render('_phoneformfields', ['form' => $form, 'phone' => $model]); ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
