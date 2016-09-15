<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\helpers\OptionHelper;
use yii\bootstrap\Modal;
use kartik\select2\Select2;

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
				'attribute' => 'payor_type_filter',
    			'width' => '140px',
    			'value' => 'payorText',
    			'label' => 'Type',
            	'filterType' => GridView::FILTER_SELECT2,
            	'filter' => array_merge(["" => ""], $payorPicklist),
            	'filterWidgetOptions' => [
            			'size' => \kartik\widgets\Select2::SMALL,
            			'hideSearch' => true,
            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
            	],
       		],
        	[
        			'attribute' => 'payor_nm',
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
			],
            [
            		'attribute' => 'received_amt',
            		'contentOptions' => ['class' => 'right'],
			],
    		[
    				'attribute' => 'feeTypes',
    				'value' => 'feeTypeTexts',
    				'format'  => 'ntext',
    				'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
    		[
    			'class' => 'yii\grid\ActionColumn',
    			'template' => '{view} {update}',
    			'buttons' => [
    				'view' => function($url, $model, $key) {
    							return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => 'View']);
    				},
    				'update' => function($url, $model, $key) {
    							  return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['title' => 'Update']);
    				},
    			],
    			'urlCreator' => function ($action, $model, $key, $index) {
					    			if ($action === 'view') {
					    				$route = ($model->payor_type == 'C') ? '/receipt-contractor' : '/receipt-member';
					    				$url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model->id]);
					    				return $url;
					    			} elseif ($action === 'update') {
					    				$url = Yii::$app->urlManager->createUrl(['/receipt/update', 'id' => $model->id]);
					    				return $url;
					    			}
				},
    			 
    			'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
    	],
    ]); ?>



</div>
<?= $this->render('../partials/_modal') ?>