<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\ReceiptContractor */
/* @var $modelResponsible app\models\accounting\ResponsibleEmployer */

// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);

$this->title = 'Create Employer Receipt';
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="receipt-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'enableClientValidation' => true,
    		'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>
    
    <?php 
    $contractor = empty($model->responsible->license_nbr) ? 'Search for an employer...' : $model->responsible->employer->contractor;
    ?>
    
    <?= $form->field($model->responsible, 'license_nbr')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
    	'initValueText' => $contractor,
    	'pluginOptions' => [
	        'allowClear' => true,
	        'minimumInputLength' => 3,
	        'ajax' => [
	            'url' => $url,
	            'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term}; }'),
	        ],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(employer) { return employer.text; }'),
			'templateSelection' => new JsExpression('function(employer) { return employer.text; }'),
	    ],
	]); ?>
    
    <?= $this->render('../receipt/_formfields', [
    	'form' => $form,
        'model' => $model,
    ]) ?>
    
    <?= $form->field($model, 'unallocated_amt')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'helper_dues')->textInput(['maxlength' => true, 'id' => 'helperdues']) ?>

    <?= $form->field($model, 'helper_hrs')
    		 ->textInput(['maxlength' => true, 'id' => 'helperhrs'])
    		 ->label('Hours', ['id' => 'helperhrslbl']) ?>
    
    
    <?= $form->field($model, 'fee_types')->checkboxList($model->getFeeOptions($model->lob_cd), [
    		'multiple' => true,
    ]) ?>
    
    <hr>

    <?= $form->field($model, 'populate')->checkbox(); ?>

    <hr>

    <?= $form->field($model, 'xlsx_file')->widget(FileInput::className(), [
    		'options' => ['accept' => '.xlsx, .xls'],
    		'pluginOptions'=> [
    				'showUpload' => false,
    		],    		
    ]);    ?>
    
    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php 
$script = <<< JS

$(function() {
		$('#helperhrslbl').hide();
		$('#helperhrs').hide();
});

$('#helperdues').change(function() {
	var dues = $(this).val();
	if(dues > 0.00) {
		$('#helperhrslbl').show();
		$('#helperhrs').show();
		$('#helperhrs').focus();
	} else {
		$('#helperhrslbl').hide();
		$('#helperhrs').hide();
	};
});

JS;
$this->registerJs($script);
?>
