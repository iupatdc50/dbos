<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\datecontrol\DateControl;
use app\helpers\OptionHelper;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\Assessment */

?>

<div class="assessment-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
       		'id' => 'assessment-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'fee_type')->widget(Select2::className(), [
    		'data' => $model->feeOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>
    
    <?= $form->field($model, 'assessment_dt')->widget(DateControl::className(), [
			'type' => DateControl::FORMAT_DATE,
	])   ?>
       
    <?= $form->field($model, 'assessment_amt')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'purpose')->textArea(['rows' => 2]) ?>
    

            
    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

