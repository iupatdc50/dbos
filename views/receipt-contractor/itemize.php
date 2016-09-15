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
?>
<div class="receipt-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    		<div class="pull-right">
    		<?php if ($modelReceipt->outOfBalance != 0.00): ?>
    			<span class="lbl-danger"><?= Html::encode('Out of Balance: ' . $modelReceipt->outOfBalance); ?></span>
			<?php endif ?>
    		</div>
            <div><p>
            		<?php if ($modelReceipt->outOfBalance == 0.00): ?>
            			<?= Html::a('Post', ['/staged-allocation/post', 'receipt_id' => $modelReceipt->id], ['class' => 'btn btn-primary']) ?>
            		<?php else: ?>
            			<?=  Html::button('<i class="glyphicon glyphicon-check"></i>&nbsp;Balance', 
							['value' => Url::to(["balance", 'id' => $modelReceipt->id, 'fee_types' => $fee_types]),
									'id' => 'balanceButton',
									'class' => 'btn btn-default btn-modal',
									'data-title' => 'Unallocated',
							]); ?>
            		<?php endif ?>
            		<?= Html::a('Cancel', ['delete', 'id' => $modelReceipt->id], [
       	            		'class' => 'btn btn-danger',
	            			'data' => [
	                			'confirm' => 'Are you sure you want to cancel this receipt?',
	                			'method' => 'post',
	            			],
	           		]) ?>
			</p></div>
    
    <?= $this->render('../receipt/_detail', ['modelReceipt' => $modelReceipt]); ?>
    
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
    						'inputType' => \kartik\editable\Editable::INPUT_MONEY,
    						'formOptions' => ['action' => '/staged-allocation/edit-alloc'],
    						'showButtons' => false,
    				],
    				'hAlign' => 'right',
    				'vAlign' => 'middle',
    				'format' => ['decimal', 2],
//    				'pageSummary' => true,
//    				'refreshGrid' => true,
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
//    	'showPageSummary' => true,
        		
    ]);?>
    
        
    
</div>
<?= $this->render('../partials/_modal') ?>

