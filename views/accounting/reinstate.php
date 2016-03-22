<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\ReinstateForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="reinstate-form">

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
    		'id' => 'receipt-form',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
    		'enableClientValidation' => true,
    		
    ]); ?>
    
    <?= $this->render('../receipt/_formfields', [
    	'form' => $form,
        'model' => $modelReceipt,
    ]) ?>

	<div class="panel panel-default">
		<div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-tasks"></i> Reinstatement Fees</h4></div>
	    <div class="panel-body">
    
    <?= $this->render('../receipt/_formalloc', [
    		'form' => $form,
    		'ixM' => -1,
    		'modelReceipt' => $modelReceipt,
    		'modelsAllocation' => $modelsAllocation, 
    ]) ?>
    
    </div></div>
    <div class="form-group">
        <?= Html::submitButton('Reinstate', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

        

</div>