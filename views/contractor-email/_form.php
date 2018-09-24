<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\contractor\Email */

?>
<div class="contractor-email-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
    		'id' => 'member-class-form',
    		'enableClientValidation' => true,
    		'enableAjaxValidation' => true,
    		
    ]); ?>

    <?= $this->render('_formfields', [
            'form' => $form,
            'email' => $model,
    ]) ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>


</div>
