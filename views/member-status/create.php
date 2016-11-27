<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use app\models\member\Status;

/* @var $this yii\web\View */
/* @var $model app\models\member\Status */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="status-form">

    <?php $form = ActiveForm::begin([
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
    		'id' => 'member-status-form',
    		'enableClientValidation' => true,
    		
    ]); ?>

    <?= $form->field($model, 'lob_cd')->widget(Select2::className(), [
    		'data' => $model->lobOptions, 
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Local...'],
    ]) ?>

    <?= $form->field($model, 'member_status')->widget(Select2::className(), [
    		'data' => $model->statusOptions, 
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Status...'],
    ]) ?>
    
    <?= $form->field($model, 'effective_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
    
    <?php if($model->reason == Status::REASON_CCD): ?>
    	<?= $form->field($model, 'reason')->hiddenInput()->label(false) ?>
    	<?= $form->field($model, 'other_local')->textInput(['maxlength' => 10])->label('Previous Local') ?>    
    <?php else: ?>
    	<?= $form->field($model, 'reason')->textarea(['rows' => 6]) ?>
	<?php endif; ?>
	
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
