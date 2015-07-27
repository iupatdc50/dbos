<?php

/**
 * Fields used for all project data entry  
 * 
 * Rendered from all project edit forms.  If the project context is LMA, 
 * is_maint field is rendered. 
 * @see app\models\project\lma\Project
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\FileInput;
use kartik\datecontrol\DateControl;
use wbraganca\dynamicform\DynamicFormWidget;
use app\helpers\OptionHelper;


/* @var $this yii\web\View */
/* @var $model app\models\project\BaseProject */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="project-fields">

	<?= $form->field($model, 'project_nm')->textInput(['maxlength' => 100]) ?>

    <?= $form->field($model, 'general_contractor')->textInput(['maxlength' => 100]) ?>

    <?= $form->field($model, 'disposition')->widget(Select2::className(), [
    		'data' => OptionHelper::getDispOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ])   ?>
    
    <?php if($this->context->id == 'project-lma'): ?>
	   	<?= $form->field($model, "is_maint")->widget(Select2::className(), [
	    		'data' => OptionHelper::getTFOptions(), 
	    		'hideSearch' => true,
				'size' => Select2::SMALL,
	    		'options' => ['placeholder' => 'Select...'],
	    ]) ?>
	<?php endif ?>

	<?= $form->field($model, 'close_dt')->widget(DateControl::className(), [
			'type' => DateControl::FORMAT_DATE,
	])   ?>
    
    <?php if ($model->isNewRecord): ?>
    <?= $this->render('../partials/_addressformfields',
    			[
    				'form'	=> $form,
    				'address' => $address,
    			]
    	) ?>
	<?php endif ?>
	

</div>
