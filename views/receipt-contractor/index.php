<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\accounting\ReceiptSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Employer Receipts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="receipt-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Receipt', ['create'], ['class' => 'btn btn-success']),
		],
    	'columns' => [
            'id',
            'received_dt',
        	[
        			'attribute' => 'payor_nm',
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
			],
        	[
        			'attribute' => 'payment_method',
        			'value' => 'methodText',
        	],
            [
            		'attribute' => 'received_amt',
            		'contentOptions' => ['class' => 'right'],
			],

            [
            		'class' => 'yii\grid\ActionColumn',
            		'contentOptions' => ['style' => 'white-space: nowrap;'],
    		],
        ],
    ]); ?>

</div>
