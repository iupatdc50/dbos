<?php

/**
 * Contractor data entry form partial
 *
 * On create, a single address and single phone may be entered.
 */

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\widgets\Select2;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $modelAddress app\models\contractor\Address */
/* @var $modelsAddress \yii\db\ActiveQuery */
/* @var $modelPhone app\models\contractor\Phone */
/* @var $modelsPhone \yii\db\ActiveQuery */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contractor-form">

    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>

    <?= $form->field($model, 'license_nbr')->textInput(['maxlength' => 8]) ?>

    <?= $form->field($model, 'contractor')->textInput(['maxlength' => 60]) ?>

    <?= $form->field($model, 'contact_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 50]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => 100]) ?>
    
    <?= $form->field($model, 'pdca_member')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    		'pluginOptions' => ['allowClear' => true],
    ]) ?>

    <?= $form->field($model, 'cba_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
    <?php if ($model->isNewRecord): ?>
    	<?= $this->render('../partials/_addressformfields',
    			[
    				'form'	=> $form,
    				'address' => $modelAddress,
    			]
    	) ?>
    	<?= $this->render('../partials/_phoneformfields',
    			[
    				'form'	=> $form,
    				'phone' => $modelPhone,
    			]
    	) ?>
	<?php endif ?>
    	
	    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

    <?php if (!$model->isNewRecord): ?>
<hr>    
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
	<?php endif ?>
    
    <?= $this->render('../partials/_modal') ?>
	    


