<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;

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
//    		'layout' => 'horizontal',
    ]); ?>
    
    <?= $form->field($model->responsible, 'license_nbr')->widget(Select2::classname(), [
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
    
    <?= $this->render('../receipt/_formfields', [
    	'form' => $form,
        'model' => $model,
    ]) ?>
    
    <?php // ** Temporary ** Assume 1791 ?>
    <?= $form->field($model, 'fee_types')->checkboxList($model->getFeeOptions('1791'), [
    		'multiple' => true,
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>
