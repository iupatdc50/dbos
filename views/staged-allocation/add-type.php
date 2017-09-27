<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;

// $model->fee_types holds the existing fee types already on the receipt

?>

<div class="allocation-type-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'allocation-type-form', 
    		'enableClientValidation' => true,
    ]); ?>
    
    <?= $form->field($model, 'lob_cd')->widget(Select2::className(), [
    		'data' => $model->lobOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => [
    				'placeholder' => 'Select Local...',
    				'id' => 'lob-cd',
    		],
    ]) ?>
    
    <?= Html::hiddenInput('fee_types', $model->serializedFeeTypes, ['id' => 'fee-types']); ?>

	<?= $form->field($model, 'new_fee_type')->widget(DepDrop::classname(), [
			'type' => DepDrop::TYPE_SELECT2,
			'select2Options' => [
					'size' => Select2::SMALL,
			],
			'pluginOptions' => [
				'depends' => ['lob-cd'],
		        'placeholder' => 'Select Type...',
				'params' => ['fee-types'],
		        'url' => Url::to(['/staged-allocation/fee-type']),
	    	]
	]) ?>

    <div class="form-group">
        <?= Html::submitButton('Add', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    

</div>