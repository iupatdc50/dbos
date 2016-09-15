<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

echo GridView::widget([
		'id' => 'receipt-grid',
		'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
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
						'attribute' => 'received_amt',
						'value' => 'receipt.received_amt', 
						'label' => 'Paid',
						'format' => ['decimal', 2],
						'hAlign' => 'right',
    			],
    			[
		    			'class' => 'yii\grid\ActionColumn',
//    					'contentOptions' => ['style' => 'white-space: nowrap;'],
    					'template' => '{view}',
            	],
		],
]);



?>

