<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\report\BaseSettingsForm */

?>

<h1>Dues Status Report Settings</h1>

<div class="dues-status">

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
       		'id' => 'contractor-info', 
    ]); ?>

    <?= $this->render('_dateformfields', [
        'model' => $model,
    	'form' => $form,
    ]) ?>

    <?= $form->field($model, 'options')->multiselect($model->getOptions(), [
//    		'min-height' => '90px',
    ]) ?>
    
    <?= $this->render('_baseformfields', [
        'model' => $model,
    	'form' => $form,
    ]) ?>

    <?php ActiveForm::end(); ?>
    
</div>
