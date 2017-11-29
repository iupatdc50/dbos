<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use kartik\editable\Editable;


$this->title = 'Build Member Receipt ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Member Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $modelReceipt->id;

?>
<div class="receipt-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="leftside sixty-pct">
    	<?= $this->render('../receipt/_stagetoolbar', ['modelReceipt' => $modelReceipt, 'controller' => '/receipt-member']); ?>
    	<?= $this->render('../receipt/_detail', ['modelReceipt' => $modelReceipt]); ?>
    </div>
    
    
    <div class="rightside thirtyfive-pct">
    <?= GridView::widget([
    	'id' => 'itemize-grid',
        'dataProvider' => $allocProvider,		
    	'pjax' => false,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=> '<i class="glyphicon glyphicon-tasks"></i>&nbsp;Receipt Allocations',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
        'columns' => [
        		'fee_type',
        		[
        				'attribute' => 'allocation_amt',
        				'class' => 'kartik\grid\EditableColumn',
        				'editableOptions' => [
        						'formOptions' => ['action' => '/allocation/edit-alloc'],
        						'showButtons' => false,
        						'buttonsTemplate' => '{submit}',
        						'asPopover' => false,
        						'pluginEvents' => [
        								'editableSuccess' => "function(event, val, form) { refreshToolBar($modelReceipt->id); }",
        						],
        				],
        				'hAlign' => 'right',
        				'vAlign' => 'middle',
        				'format' => ['decimal', 2],
        				
    			],
        		[
        			'class' => 'kartik\grid\ActionColumn',
					'template' => '{delete}',
					'buttons' => [
						 	'details' => function ($url, $model) {
						        			return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
						                				'title' => Yii::t('app', 'Remove'),
						        						'data-confirm' => 'Are you sure you want to delete this item?',
						        			]);
						    }
					],
					'urlCreator' => function ($action, $model, $key, $index) {
						if ($action === 'delete') {
							$url = '/allocation/delete?id=' . $model->id;
						    return $url;
						}
					},
        			'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
        						[
        								'value' => Url::to(["/allocation/create", 'alloc_memb_id' => $modelReceipt->allocatedMembers[0]->id]),
        								'id' => 'allocationCreateButton',
        								'class' => 'btn btn-default btn-modal btn-embedded',
        								'data-title' => 'Allocation',
        						]),
        				
        		],        		
    	],
//    	'showPageSummary' => true,
        		
    ]);?>
    </div>
    
        
    
</div>
<?= $this->render('../partials/_modal') ?>

<?php
$script = <<< JS

$('.kv-editable-link').on('focus', function() {
	$(this).trigger('click');	
});

JS;
$this->registerJs($script);
?>
