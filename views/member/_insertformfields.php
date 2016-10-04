<?php

/**
 * Member data entry form partial
 *
 * On create, a single address and single phone may be entered.
 */

use yii\helpers\Html;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use kartik\checkbox\CheckboxX;

/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $modelAddress app\models\member\Address */
/* @var $modelPhone app\models\member\Phone */
/* @var $modelEmail \yii\db\ActiveQuery */
/* @var $modelStatus \yii\db\ActiveQuery */
/* @var $modelClass \yii\db\ActiveQuery */
/* @var $form yii\widgets\ActiveForm */
?>

    	<hr>
    	<?= $this->render('../partials/_addressformfields',
    			[
    				'form'	=> $form,
    				'address' => $modelAddress,
    			]
    	) ?>
    	<hr>
    	<?= $this->render('../partials/_phoneformfields',
    			[
    				'form'	=> $form,
    				'phone' => $modelPhone,
    			]
    	) ?>
    	<hr>
    	<?= $form->field($modelEmail, 'email')->textInput(['maxlength' => 50]) ?>

    	<?= $form->field($modelStatus, 'lob_cd')->widget(Select2::className(), [
    		'data' => $modelStatus->lobOptions,
    		'hideSearch' => false,
    		'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    	]); ?>
    	
    	<?= $this->render('../partials/_memberclassformfields', [
    			'form' => $form,
    			'modelClass' => $modelClass,
    	]) ?>
    	    	
    	<hr>
    	
    	<?= $form->field($model, 'exempt_apf')->widget(CheckboxX::className(), ['pluginOptions' => ['threeState' => false]]); ?>
    
	    
