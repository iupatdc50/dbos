<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\employment\Employment */

// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);


?>
<div class="employment-create">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'employ-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'effective_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
    
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
	
    <div class="form-group">
        <?= Html::submitButton('Loan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
	        
    
</div>
