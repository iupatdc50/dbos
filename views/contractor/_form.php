<?php

/**
 * Contractor data entry form partial
 *
 * On create, a single address and single phone may be entered.
 */

use yii\helpers\Html;
use kartik\form\ActiveForm;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $modelSig app\models\contractor\Signatory */
/* @var $modelAddress app\models\contractor\Address */
/* @var $modelsAddress \yii\db\ActiveQuery */
/* @var $modelPhone app\models\contractor\Phone */
/* @var $modelsPhone \yii\db\ActiveQuery */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contractor-form">

    <?php $form = ActiveForm::begin([
    		'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
//    		'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <div class="leftside fortyfive-pct">
    
    <?= $form->field($model, 'license_nbr')->textInput(['maxlength' => 8]) ?>

    <?= $form->field($model, 'contractor')->textInput(['maxlength' => 60]) ?>

    <?= $form->field($model, 'contact_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 50]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => 100]) ?>
    
    <?php if ($model->isNewRecord): ?>
    	<?= $this->render('../contractor-signatory/_formfields', ['modelSig' => $modelSig, 'form' => $form]); ?>
	<?php endif ?>
    
    <hr>
	    <div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
        
    </div>
    
    <?php if ($model->isNewRecord): ?>
    	<div class="rightside fifty-pct">
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
    	</div>
	<?php endif ?>
    	

    <?php ActiveForm::end(); ?>
</div>

    <?php if (!$model->isNewRecord): ?>
	<hr>    
    <div class="rightside fifty-pct">

    	<?= $this->render(
	    		'../partials/_addressgrid',
	    		[
	    			'modelsAddress' => $modelsAddress,
	    			'controller' => 'contractor-address',
	    			'relation_id' => $model->license_nbr,	
	    		]
	    ) ?>
    
	    <?= $this->render(
	    		'../partials/_phonegrid',
	    		[
	    			'modelsPhone' => $modelsPhone,
	    			'controller' => 'contractor-phone',
	    			'relation_id' => $model->license_nbr,	
	    		]
	    ) ?>
	    
	</div>
	<?php endif ?>
    
    <?= $this->render('../partials/_modal') ?>
	    


