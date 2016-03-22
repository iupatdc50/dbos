<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $modelReceipt app\models\accounting\ReceiptMember */
/* @var $modelMember app\models\accounting\AllocatedMember */

// The controller action that will render the list
$url = Url::to(['/member/member-list']);

$this->title = 'Create Member Receipt';
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="receipt-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
    		'id' => 'receipt-form',
    ]); ?>
    
    <?= $form->field($modelMember, 'member_id')->widget(Select2::classname(), [
//		'size' => Select2::SMALL,
    	'options' => ['placeholder' => 'Search for a member...'],
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
        'model' => $modelReceipt,
    ]) ?>

	<div class="panel panel-default">
		<div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-tasks"></i> Receipt Allocations</h4></div>
	    <div class="panel-body">
    
    <?= $this->render('../receipt/_formalloc', [
    		'form' => $form,
    		'ixM' => -1,
    		'modelReceipt' => $modelReceipt,
    		'modelsAllocation' => $modelsAllocation, 
    ]) ?>
    
    </div></div>
    
    
    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>
