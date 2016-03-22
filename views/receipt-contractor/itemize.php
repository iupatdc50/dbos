<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;


// The controller action that will render the list
$url = Url::to(['/member/member-list']);

$this->title = 'Build Employer Receipt ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Employer Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $modelReceipt->id;
?>
<div class="receipt-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= DetailView::widget([
        'model' => $modelReceipt,
        'attributes' => [
            'received_dt:date',
        	[
            		'attribute' => 'payor_nm',
            		'value' => $modelReceipt->payor_nm . ($modelReceipt->payor_type == 'O' ? ' (for ' . $modelReceipt->responsible->employer->contractor . ')' : ''),
    		],
            [
            		'attribute' => 'payment_method',
            		'value' => Html::encode($modelReceipt->methodText) . ($modelReceipt->payment_method != '1' ? ' [' . $modelReceipt->tracking_nbr . ']' : ''),
   			],
            'received_amt',
            'unallocated_amt',
        ],
    ]) ?>
    
    <?php $form = ActiveForm::begin(['id' => 'receipt-form']); ?>
    
<!-- Members -->
    	<?php DynamicFormWidget::begin([
    			'widgetContainer' => 'dynamicform_wrapper',
            	'widgetBody' => '.container-members', // required: css class selector
            	'widgetItem' => '.member', // required: css class
            	'insertButton' => '.add-member', // css class
            	'deleteButton' => '.del-member', // css class
            	'model' => $modelsMember[0],
            	'formId' => 'receipt-form',
            	'formFields' => [
                	'fullName',
            		'report_id',
           		],
    	]); ?>
    	
	<div class="panel panel-default">
		<div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-tasks"></i> Receipt Allocations</h4></div>
	    <div class="panel-body">
       	<table class="table table-bordered table-striped">
    		<thead>
    			<tr class="active">
    				<td class="thirtyfive-pct"><?= Html::activeLabel($modelsMember[0]->member, 'fullName'); ?></td>
    				<td><?= Html::activeLabel($modelsMember[0]->member, 'report_id'); ?></td>
    				<td><label class="control-label">Allocations</label></td>
    				<td><button type="button" class="add-member btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button></td>
    			</tr>
    		</thead>
    		<tbody class="container-members">
    		<?php foreach ($modelsMember as $ixM => $member): ?>
    			<tr class="member">
    				
    				
    				<td>
    <?= $form->field($member->member, "[{$ixM}]fullName")->label(false)->widget(Select2::classname(), [
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
    				
    				</td>

    				<td><?= $form->field($member->member, "[{$ixM}]report_id")->label(false)->textInput(['maxlength' => true]) ?></td>
     				
    				<td class="forty-pct">
    					<?= $this->render('../receipt/_formalloc', [
    							'form' => $form,
    							'ixM' => $ixM,
    							'modelReceipt' => $modelReceipt,
    							'modelsAllocation' => $modelsAllocation[$ixM], 
    					]) ?>
    				</td>
					<td>
                        <button type="button" class="del-member btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                        <?php
                        // necessary for update action.
                        if (! $member->isNewRecord) {
                            echo Html::activeHiddenInput($member, "[{$ixM}]id");
                        }
                        ?>
    				</td>
    			</tr>
    		<?php endforeach; ?>
    		</tbody>
    	</table></div></div>
    	<?php DynamicFormWidget::end(); ?>
    	

    <p>
        <?= Html::a('Submit', ['itemize', 'id' => $modelReceipt->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['delete', 'id' => $modelReceipt->id], [
            'class' => 'btn btn-default',
            'data' => [
                'confirm' => 'Are you sure you want to cancel this receipt? (All data will be lost)',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    
    <?php ActiveForm::end(); ?>
    
    
</div>
