<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $model app\models\member\Member */

?>
<div class="member-create">


    <?php $form = ActiveForm::begin([
    		'id' => 'member-stub-form',
    		'layout' => 'horizontal',
    		'enableClientValidation' => true,
    		'enableAjaxValidation' => true,
    		
    ]); ?>
        
    <?= $form->field($model, 'last_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'first_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'middle_inits')->textInput(['maxlength' => 7]) ?>

    <?= $form->field($model, 'suffix')->textInput(['maxlength' => 7]) ?>

    <?= $form->field($model, 'gender')->widget(Select2::className(), [
    		'data' => OptionHelper::getGenderOptions(), 
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    	<?= $form->field($modelStatus, 'lob_cd')->widget(Select2::className(), [
    		'data' => $modelStatus->lobOptions,
    		'hideSearch' => false,
    		'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    	]); ?>
    	    	
	    <?= $form->field($model, 'application_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    	])->label('Date') ?>
	
	<?= $form->field($model, 'ssnumber')->textInput(['maxlength' => 11]) ?>

	    <hr>
	    <div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
 
    <?php ActiveForm::end(); ?>
	    
    
</div>
