<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\FileInput;
use kartik\datecontrol\DateControl;
use app\helpers\OptionHelper;

// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);

$bidder_nm = $model->isNewRecord ? '' : $model->biddingContractor->contractor;

?>

<div class="registration-fields">
				
	<?= $form->field($model, 'bidder')->widget(Select2::classname(), [
		'initValueText' => $bidder_nm,
		'size' => Select2::SMALL,
		'options' => ['placeholder' => 'Search for contractor...'],
		'pluginOptions' => [
			'allowClear' => true,
			'minimumInputLength' => 3,
			'ajax' => [
				'url' => $url,
				'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term,agreement_type:"LMA"}; }'),
			],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(bidder) { return bidder.text; }'),
			'templateSelection' => new JsExpression('function(bidder) { return bidder.text; }'),
		],
	]) ?>
	<?= $form->field($model, "bid_dt")->widget(DateControl::className(), ['type' => DateControl::FORMAT_DATE]) ?>
	
	<?php if($model->is_maint): ?>
	
	<div class="form-group">
	    <?= Html::activeLabel($model, 'estimated_hrs', ['label'=>'Hours', 'class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'estimated_hrs', [
	        		'showLabels'=>false, 
	        		'addon' => [
	        				'prepend' => ['content' => 'from'],
	        		]
	        		
	        ])->textInput(['placeholder'=>'Min Hours']); ?>
	    </div>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'estimated_hrs_to', [
	        		'showLabels'=>false,
	        		'addon' => [
	        				'prepend' => ['content' => 'to'],
	        		]

	        ])->textInput(['placeholder'=>'Max Hours']); ?>
	    </div>
	</div>
	<div class="form-group">
	    <?= Html::activeLabel($model, 'estimate', ['label'=>'Amount', 'class'=>'col-sm-2 control-label']) ?>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'estimate', [
	        		'showLabels'=>false,
	        		'addon' => [
	        				'prepend' => ['content' => 'from'],
	        		]
	        		 
	        ])->textInput(['placeholder'=>'Min Amount']); ?>
	    </div>
	    <div class="col-sm-4">
	        <?= $form->field($model, 'estimate_to', [
	        		'showLabels'=>false,
	        		'addon' => [
	        				'prepend' => ['content' => 'to'],
	        		]
	        		 
	        ])->textInput(['placeholder'=>'Max Amount']); ?>
	    </div>
	</div>
	
	<?php else: ?>
	<?= $form->field($model, "estimated_hrs")->textInput(['maxlength' => true]) ?>
	<?= $form->field($model, "estimate")->textInput(['maxlength' => true]) ?>
	<?php endif; ?>
	
	<?= $form->field($model, "doc_file")->widget(FileInput::className(), [
    		'options' => ['accept' => 'application/pdf'],
    		'pluginOptions'=> [
    				'allowedFileExtensions'=>['pdf','png'],
    				'showUpload' => false,
    		],
    ]); ?>
	
</div>