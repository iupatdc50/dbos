<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\DepDrop;

// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);
?>

<div class="remit-create">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'remit-create',
    		'enableClientValidation' => true,
    		'options' => ['enctype' => 'multipart/form-data'],
    ]); 
    
    $contractor = empty($remitForm->license_nbr) ? 'Search for an employer...' : $modelContractor->contractor;
    ?>

    <?= $form->field($remitForm, 'license_nbr')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
    	'options' => ['id' => 'license-nbr'],
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
	
	<?= $form->field($remitForm, 'lob_cd')->widget(DepDrop::className(), [
			'type'=>DepDrop::TYPE_SELECT2,
			'select2Options'=>['size' => Select2::SMALL],
			'pluginOptions' => [
					'depends' => ['license-nbr'],
					'url' => Url::to(['/contractor/lob-picklist']),
					'initialize' => true,
			], 
	]); ?>
    
    <div class="form-group">
        <?= Html::submitButton('Create Spreadsheet', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
	    

</div>
