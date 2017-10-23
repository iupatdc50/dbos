<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use kartik\editable\Editable;


$this->title = 'Build Employer Receipt ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Employer Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $modelReceipt->id;

// $url = ["balance", 'id' => $modelReceipt->id, 'fee_types' => $fee_types];

?>
<div class="receipt-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('../receipt/_stagetoolbar', ['modelReceipt' => $modelReceipt]); ?>
    <?= $this->render('../receipt/_detail', ['modelReceipt' => $modelReceipt]); ?>
    
    <?php
    	$baseColumns = [
	        	[
	        		'class' => 'yii\grid\ActionColumn',
	        		'contentOptions' => ['style' => 'width:50px'],
	            	'template' => '{reassign}',
	            	'buttons' => [
	        			'reassign' => function ($url, $model) {
	            						return Html::button('<i class="glyphicon glyphicon-transfer"></i><i class="glyphicon glyphicon-user"></i>',
	            								[
	            										'value' => Url::to(['/staged-allocation/reassign', 'id' => $model->alloc_memb_id]),
	            										'id' => 'reassignButton',
	            										'class' => 'btn btn-default btn-modal btn-embedded',
	            										'title' => 'Re-assign allocation',
	            										'data-title' => 'Reassign',
	            								]);
	        		        		},
	        		        		
	        				
	        		],
	        	],
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
    		$feeColumns[] = [
    				'attribute' => $fee_type,
    				'header' => strtoupper($fee_type),
    				'class' => 'kartik\grid\EditableColumn',
    				'editableOptions' => [
    						'header' => strtoupper($fee_type),
    						'formOptions' => ['action' => '/staged-allocation/edit-alloc'],
    						'showButtons' => false,
    						'asPopover' => false,
    				],
    				'hAlign' => 'right',
    				'vAlign' => 'middle',
    				'format' => ['decimal', 2],
    		];
    	} 
    	$header =   
    		Html::button('<i class="glyphicon glyphicon-option-horizontal"></i>',
      			['value' => Url::to(["/staged-allocation/add-type", 'receipt_id' => $modelReceipt->id, 'fee_types' => $fee_types]),
      					'id' => 'allocationCreateTypeButton',
      					'class' => 'btn btn-default btn-modal',
      					'title' => 'Add fee type column',
      					'data-title' => 'Fee Type',
      		]) .' '.
    		Html::button('<i class="glyphicon glyphicon-plus"></i><i class="glyphicon glyphicon-user"></i>',
      			['value' => Url::to(["/staged-allocation/add", 'receipt_id' => $modelReceipt->id, 'fee_types' => $fee_types]),
      					'id' => 'allocationCreateButton',
      					'class' => 'btn btn-default btn-modal',
      					'title' => 'Add member allocation line',
      					'data-title' => 'Allocation',
      		]);
      	
    	$actionColumn[] = [
					'class' => 'kartik\grid\ActionColumn',
					'controller' => 'staged-allocation',
					'template' => '{delete}',
					'header' => $header,
            		'width' => '110px',
    	];
    ?>
    
    <?= GridView::widget([
    	'id' => 'itemize-grid',
        'dataProvider' => $allocProvider,		
        'filterModel' => $searchAlloc,
 		'filterRowOptions'=>['class'=>'filter-row'],
    	'pjax' => true,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=> '<i class="glyphicon glyphicon-tasks"></i>&nbsp;Receipt Allocations',
				'before' => false,
				'after' => false,
		],
        'columns' => array_merge($baseColumns, $feeColumns, $actionColumn),
//    	'showPageSummary' => true,
        		
    ]);?>
    
        
    
</div>
<?= $this->render('../partials/_modal') ?>

