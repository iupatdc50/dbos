<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\grid\GridView;
use yii\bootstrap\Modal;


$this->title = 'Build Employer Receipt ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Employer Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $modelReceipt->id;
?>
<div class="receipt-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
            <div><p>
            		<?= Html::a('Post', ['*'], ['class' => 'btn btn-primary']) ?>
            		<?= Html::button('<i class="glyphicon glyphicon-print"></i>&nbsp;Print',
							['value' => Url::to(["*", 'receipt_id' => $modelReceipt->id, 'fee_types' => $fee_types]),
									'id' => 'printButton',
									'class' => 'btn btn-default btn-modal',
									'data-title' => 'Print',
							]); ?>
			</p></div>
    
    
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
            [
            		'attribute' => 'outOfBalance',
            		'label' => 'Out of Balance',
           // 		'rowOptions' => ($modelReceipt->outOfBalance != 0.00) ? ['class' => 'danger'] : ['class' => 'success'],
            		'rowOptions' => ['class' => 'danger'],
        	],
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
    		$feeColumns[] = [
    				'attribute' => $fee_type,
    				'header' => strtoupper($fee_type),
    				'class' => 'kartik\grid\EditableColumn',
    				'editableOptions' => [
    						'header' => strtoupper($fee_type),
    						'inputType' => \kartik\editable\Editable::INPUT_TEXT,
    						'formOptions' => ['action' => '/staged-allocation/edit-alloc'],
    				],
    				'hAlign' => 'right',
    				'vAlign' => 'middle',
    				'format' => ['decimal', 2],
    		];
    	} 
    	$actionColumn[] = [
					'class' => 'kartik\grid\ActionColumn',
					'controller' => 'staged-allocation',
					'template' => '{delete}',
					'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
							['value' => Url::to(["/staged-allocation/add", 'receipt_id' => $modelReceipt->id, 'fee_types' => $fee_types]),
									'id' => 'allocationCreateButton',
									'class' => 'btn btn-default btn-modal btn-embedded',
									'data-title' => 'Allocation',
							]),
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
        		
    ]);?>
    
        
    
</div>
<?= $this->render('../partials/_modal') ?>