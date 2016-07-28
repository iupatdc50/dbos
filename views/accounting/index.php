<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\helpers\OptionHelper;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\accounting\ReceiptSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Receipts';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="receipt-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::button('Create Receipt', 
					[
							'class' => 'btn btn-success btn-modal',
							'id' => 'receiptCreateButton',
							'value' => Url::to(["create-receipt"]),
							'data-title' => 'Receipt',
					]),
		],
    	'rowOptions' => function($model) {
    		$css = ['verticalAlign' => 'middle'];
    		return $css;
    		},
    	'columns' => [
    		[
    				'attribute' => 'id',
    				'label' => 'Nbr',
    		],
    		[
    				'attribute' => 'received_dt',
    				'format' => 'date',
    				'label' => 'Received',
    		],
        	[
        			'attribute' => 'payor_nm',
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
			],
        	[
        			'label' => 'Method',
        			'attribute' => 'payment_method',
        			'value' => 'methodText',
        	],
            [
            		'attribute' => 'received_amt',
            		'contentOptions' => ['class' => 'right'],
			],
    		[
    				'attribute' => 'remarks',
//    				'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
    		[
    			'class' => 'yii\grid\ActionColumn',
    					'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
    	],
    ]); ?>



</div>
<?= $this->render('../partials/_modal') ?>