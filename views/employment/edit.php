<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * Special version of create for employment. Launched by the Create controller action.
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\checkbox\CheckboxX;


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
        
    <?= $form->field($model, 'employer')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
    	'initValueText' => $model->contractor->contractor,
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
    
    <?= $form->field($model, 'loan_ckbox')->widget(CheckboxX::className(), [
//    		'initInputType' => CheckboxX::INPUT_CHECKBOX,
    		'autoLabel' => true,
    		'pluginOptions' => [
    				'enclosedLabel' => true,
    				'threeState' => false,
    		],
    		'pluginEvents' => [
    				'change' => "function () {
    					if ($(this).val() == '1') {
    						\$('#hideable').show();
						} else {
    						\$('#hideable').hide();
						}
					}",
    		],
    ])->label(false); ?>
    
    <div id="hideable">
    <?= $form->field($model, 'dues_payor')->widget(Select2::classname(), [
    	'size' => Select2::SMALL,
    	'initValueText' => $model->duesPayor->contractor,
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
	])->label('Loan to', ['id' => 'duespayorlbl']); ?>
	</div>
	
    <div class="form-group">
        <?= Html::submitButton('Update', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php 
$script = <<< JS

$(function() {
 	if ($('#employment-loan_ckbox').val() != "1") {
		$('#hideable').hide();
 	}
})
 		

JS;
$this->registerJs($script);
?>
