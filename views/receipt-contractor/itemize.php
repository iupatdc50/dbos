<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\grid\GridView;
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
    
    <?php
    	$baseColumns = [
        		[
        				'attribute' => 'fullName', 
        				'value' => 'member.fullName',
        				
    			],
        		[
        				'attribute' => 'reportId', 
        				'value' => 'member.report_id',
        				
    			],
				
        ];
    	$feeColumns = [];
    	foreach ($fee_types as $fee_type) {
    		$feeColumns[] = ['attribute' => $fee_type];
    	} 
    	$actionColumn[] = [
					'class' => \yii\grid\ActionColumn::className(),
					'controller' => 'member-address/create',
					'template' => '{delete}',
					'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
							['value' => Url::to(["/member-address/create", 'relation_id'  => $modelReceipt->id]),
									'id' => 'employeeAddButton',
									'class' => 'btn btn-default btn-modal btn-embedded',
									'data-title' => 'Employee',
							]),
				];
    ?>
    
    <?= GridView::widget([
        'dataProvider' => $allocProvider,		
        'filterModel' => $searchAlloc,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=> '<i class="glyphicon glyphicon-task"></i>&nbsp;Receipt Allocations',
				'before' => false,
				'after' => false,
		],
        'columns' => array_merge($baseColumns, $feeColumns, $actionColumn),
        		
    ]);?>
    
        
    
</div>
