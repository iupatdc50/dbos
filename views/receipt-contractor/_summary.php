<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\accounting\ReceiptContractorSearch */

?>

<div id="contractor-receipts">

<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'contreceipt-grid', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
		'id' => 'contreceipt-grid',
		'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'pjax' => false,
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'Contractor Receipts',
				'before' => false,
				'after' => false,
		],
		'columns' => [
				[ 
						'attribute' => 'receipt_id',
						'label' => 'Nbr',
				],
				[
						'attribute' => 'received_dt',
						'value' => 'receipt.received_dt',
						'format' => 'date',
						'label' => 'Received',
				],
				[
	    				'attribute' => 'feeTypes',
	    				'value' => 'receipt.feeTypeTexts',
	    				'format'  => 'ntext',
	    				'contentOptions' => ['style' => 'white-space: nowrap;'],
	        	],
				[
				        'class' =>'kartik\grid\DataColumn',
						'attribute' => 'received_amt',
						'value' => 'receipt.received_amt', 
						'label' => 'Paid',
						'format' => ['decimal', 2],
						'hAlign' => 'right',
    			],
    			[
		    			'class' => 'yii\grid\ActionColumn',
    					'template' => '{view}',
    					'buttons' => [
    							'view' => function ($url, $model) {
    								return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', '/receipt-contractor/view?id='.$model->receipt_id, [
    										'title' => Yii::t('app', 'View'),
    								]);
    							}
    					],    					
            	],
		],
]);



?>

</div>
<?php

Pjax::end();
