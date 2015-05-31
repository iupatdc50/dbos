<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;


/* @var $this yii\web\View */
/* @var $model app\models\member\Employment */
/* @var $form yii\widgets\ActiveForm */
/* @var $create String indicates 'Employ' or 'Loan' */
?>

<?php
// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);

?>

<div class="employment-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'employ-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'effective_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
    
    <?php if ($create == 'Employ'): ?>
    
    <?= $form->field($model, 'employer')->widget(Select2::classname(), [
//    	'disabled' => ($create == 'Loan'),
		'size' => Select2::SMALL,
    	'options' => ['placeholder' => 'Search for an employer...'],
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
    
    <?= $form->field($model, 'member_pays')->checkbox() ?>
    <?php endif; ?>
    
    <?php if ($create == 'Loan'):  ?>
    <?= $form->field($model, 'dues_payor')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
    	'options' => ['placeholder' => 'Search for a loan-to contractor...', ],
	    'pluginOptions' => [
	        'allowClear' => true,
	        'minimumInputLength' => 3,
	        'ajax' => [
	            'url' => $url,
	            'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term}; }'),
	        ],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(payor) { return payor.text; }'),
			'templateSelection' => new JsExpression('function(payor) { return payor.text; }'),
	    ],
	])->label('Loan to'); ?>
	
    <?php endif; ?>
    
    <div class="form-group">
        <?= Html::submitButton($create, ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>



