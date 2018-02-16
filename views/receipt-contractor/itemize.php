<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;


/* @var $searchAlloc app\models\accounting\StagedAllocationSearch */
/* @var $allocProvider yii\data\ActiveDataProvider */
/* @var $modelReceipt app\models\accounting\ReceiptContractor */
/* @var $fee_types array */


$this->title = 'Build Employer Receipt ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Employer Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $modelReceipt->id;

$nbsp = '&nbsp;';

// $url = ["balance", 'id' => $modelReceipt->id, 'fee_types' => $fee_types];

?>
<div class="receipt-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <div id="stagetoolbar">
    <?= $this->render('../receipt/_stagetoolbar', ['modelReceipt' => $modelReceipt, 'controller' => '/receipt-contractor']); ?>
    </div>
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
	            										'tabIndex' => '-1',
	            								]);
	        		        		},
	        		        		
	        				
	        		],
	        	],
	        	[
	        			'attribute' => 'classification',
	        			'value' => 'member.classification.classification',
	        			'label' => 'Class',
	        			'width' => '5px',
	        	],
                [
                    'attribute' => 'reportId',
                    'value' => 'member.report_id',
                    'contentOptions' => ['style' => 'width:120px'],
                ],
    			[
        				'attribute' => 'fullName', 
        				'value' => 'member.fullName',
        				
    			],

        ];


    	$feeColumns = [];

        foreach ($searchAlloc->fee_types as $fee_type) {
    		$feeColumns[] = [
    				'attribute' => $fee_type,
    				'header' => strtoupper($fee_type),
    				'class' => 'kartik\grid\EditableColumn',
    				'editableOptions' => [
    						'header' => strtoupper($fee_type),
    						'formOptions' => ['action' => '/staged-allocation/edit-alloc'],
    						'showButtons' => false,
    						'asPopover' => false,
    						'buttonsTemplate' => '{submit}',
    						'pluginEvents' => [
    								'editableSuccess' => "function(event, val, form) { refreshToolBar($modelReceipt->id); }",
    						],
    				],
    				'hAlign' => 'right',
    				'vAlign' => 'middle',
    				'format' => ['decimal', 2],
    		];
    	} 
    	$header =   
    		Html::button('<i class="glyphicon glyphicon-option-horizontal"></i>',
      			['value' => Url::to(["/staged-allocation/add-type", 'receipt_id' => $modelReceipt->id]),
      					'id' => 'allocationCreateTypeButton',
      					'class' => 'btn btn-default btn-modal',
      					'title' => 'Add fee type column',
      					'data-title' => 'Fee Type',
      		]) . $nbsp .
    		Html::button('<i class="glyphicon glyphicon-plus"></i><i class="glyphicon glyphicon-user"></i>',
      			['value' => Url::to(["/staged-allocation/add", 'receipt_id' => $modelReceipt->id]),
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
	    			'buttons' => [
	    					'delete' => function ($url, $model) {
	    						return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
	    								'title' => Yii::t('app', 'Delete'),
	    								'data-confirm' => 'Are you sure you want to delete this allocation item?',
	    								'data-method' => 'post',
	    								'tabIndex' => '-1',
	    						]);
	    					}
	    			],
    			 
    			
    	];
    ?>
    
    <?php
    try {
        echo GridView::widget([
            'id' => 'itemize-grid',
            'dataProvider' => $allocProvider,
            'filterModel' => $searchAlloc,
            'filterRowOptions' => ['class' => 'filter-row'],
            'pjax' => false,
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                'heading' => '<i class="glyphicon glyphicon-tasks"></i>&nbsp;Receipt Allocations',
                'before' => '', // prevent 1 display when true
                'after' => false,
            ],
            'toolbar' => [
                'content' =>
                    Html::button('Create Member Stub', [
                        'class' => 'btn btn-default btn-modal',
                        'id' => 'memberCreateButton',
                        'value' => Url::to(["/member/create-stub"]),
                        'data-title' => 'Member Stub',
                    ]),
            ],
            'rowOptions' => function ($model) {
                $member = $model->member;
                $css = ['verticalAlign' => 'middle'];

                if (!isset($member->currentStatus) || ($member->currentStatus->member_status == \app\models\member\Status::STUB))
                    $css['class'] = 'text-muted';
                else
                    $css['class'] = 'default';
                return $css;
            },


            'columns' => array_merge($baseColumns, $feeColumns, $actionColumn),
            //    	'showPageSummary' => true,

        ]);
    } catch (Exception $e) {
    }
    ?>
    
        
    
</div>
<?= $this->render('../partials/_modal') ?>

<?php
$script = <<< JS

$('.kv-editable-link').on('focus', function() {
	$(this).trigger('click');	
});

$(document).keydown(function(e) {
    if (e.which === 107 || e.which === 187) {
        $('#allocationCreateButton').click();
    }
})

JS;
$this->registerJs($script);
?>


