<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\FileInput;
use kartik\datecontrol\DateControl;

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
				'data' => new JsExpression('function(params) { return {search:params.term,agreement_type:"JTP"}; }'),
			],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(bidder) { return bidder.text; }'),
			'templateSelection' => new JsExpression('function(bidder) { return bidder.text; }'),
		],
	]) ?>
	<?= $form->field($model, "bid_dt")->widget(DateControl::className(), ['type' => DateControl::FORMAT_DATE]) ?>
	<?= $form->field($model, "estimated_hrs")->textInput(['maxlength' => true]) ?>
	<?= $form->field($model, "subsidy_rate")->textInput(['maxlength' => true]) ?>
	<?= $form->field($model, "doc_file")->widget(FileInput::className(), [
    		'options' => ['accept' => 'application/pdf'],
    		'pluginOptions'=> [
    				'allowedFileExtensions'=>['pdf','png'],
    				'showUpload' => false,
    		],
    ]); ?>
	
</div>