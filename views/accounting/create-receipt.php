<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\DepDrop;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\Receipt */

$this->title = 'Choose Receipt Type';
// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);

?>

<div class="recptchoice-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'recptchoice-form', 
    		'enableClientValidation' => true,
    ]); ?>
    
    <?= $form->field($model, 'payor_type', ['options' => ['id' => 'payortype']])->radioList($payorOptions)->label('Select'); ?>
    
    <div id="member-option">

    <?= $form->field($model, 'member_id')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
    	'options' => ['placeholder' => 'Search for a member...'],
	    'pluginOptions' => [
	        'allowClear' => true,
	        'minimumInputLength' => 3,
	        'ajax' => [
	            'url' => Url::to(['/member/member-list']),
	            'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term}; }'),
	        ],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(member_id) { return member_id.text; }'),
			'templateSelection' => new JsExpression('function(member_id) { return member_id.text; }'),
	    ],
    	
	]); ?>
	
	</div>
    <div id="contractor-option">
	
    <?= $form->field($model, 'license_nbr')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
    	'options' => ['id' => 'license-nbr', 'placeholder' => 'Search for a contractor...'],
    	'pluginOptions' => [
	        'minimumInputLength' => 3,
	        'ajax' => [
	            'url' => Url::to(['/contractor/contractor-list']),
	            'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term}; }'),
	        ],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(employer) { return employer.text; }'),
			'templateSelection' => new JsExpression('function(employer) { return employer.text; }'),
	    ],
	]); ?>
	
	<?= $form->field($model, 'lob_cd')->widget(DepDrop::className(), [
			'type'=>DepDrop::TYPE_SELECT2,
			'select2Options'=>['size' => Select2::SMALL],
			'pluginOptions' => [
					'depends' => ['license-nbr'],
					'url' => Url::to(['/contractor/lob-picklist']),
			], 
	]); ?>
	
	</div>
    
    <div class="form-group">
        <?= Html::submitButton('Begin', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(function() {
		$('#member-option').hide();
		$('#contractor-option').hide();
});
		
$('#payortype').change(function() {
	var typ = $('#payortype').find('input:checked').val();
	$('#member-option').hide();
	$('#contractor-option').hide();
	if(typ === "M") {
		$('#member-option').show();
	} else if(typ === "C") {
		$('#contractor-option').show();
	};
});

JS;
$this->registerJs($script);
?>


    