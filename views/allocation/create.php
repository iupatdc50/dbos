<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;

/* @var $this \yii\web\View */
/* @var $model \app\models\accounting\BaseAllocation */
/* @var $feeOptions array */

?>

<div class="allocation-form">

	<?php $form = ActiveForm::begin([
		'layout' => 'horizontal',
		'options' => ['class' => 'ajax-create'], // Required for modal within an update
		'id' => 'allocation-form',
		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'fee_type')->widget(Select2::className(), [
    		'data' => $feeOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Fee Type...'],
    		
    ]) ?>
    
    <?= $form->field($model, 'allocation_amt')->textInput(['maxlength' => true]) ?>
  
	        
    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
