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

$show_class = $mine_only ? 'glyphicon glyphicon-expand' : 'glyphicon glyphicon-user';
$show_label = $mine_only ? 'All' : 'Mine Only';
$toggle_mine_only = !$mine_only;


?>

<div class="receipt-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
			// workaround to prevent 1 in the before section
			'before' => (Yii::$app->user->can('createReceipt')) ? '' : false,
		    'after' => false,
		],
		'toolbar' => [
			'content' => 
				Html::a(Html::tag('span', '', ['class' => $show_class]) . '&nbsp;Show ' . $show_label, 
							['index', 'mine_only' => $toggle_mine_only],
							['class' => 'btn btn-default'])
				.
				Html::button('Create Receipt', 
					[
							'class' => 'btn btn-success btn-modal',
							'id' => 'receiptCreateButton',
							'value' => Url::to(["create-receipt"]),
							'data-title' => 'Receipt',
					]),
		],
    	'rowOptions' => function($model) {
    		$css = ['verticalAlign' => 'middle'];
    		if ($model->void == OptionHelper::TF_TRUE)
    			$css['class'] = 'text-muted';
    		
    		return $css;
    		},
    	'columns' => [
    		[
    				'attribute' => 'id',
    				'label' => 'Nbr',
    		],
    		'lob_cd',
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
        			'value' => function($model) { return ($model->void == OptionHelper::TF_TRUE) ? '** VOID **' : $model->payor_nm; },
			],
            [
            		'attribute' => 'received_amt',
            		'contentOptions' => ['class' => 'right'],
        			'value' => function($model) { return ($model->void == OptionHelper::TF_TRUE) ? '** VOID **' : $model->received_amt; },
            ],
    		[
    				'attribute' => 'feeTypes',
    				'value' => 'feeTypeTexts',
    				'format'  => 'ntext',
    				'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
    		[
    			'class' => 'yii\grid\ActionColumn',
    			'template' => '{view}',
    			'buttons' => [
    				'view' => function($url, $model, $key) {
    							return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => 'View']);
    				},
    			],
    			'urlCreator' => function ($action, $model, $key, $index) {
					    			if ($action === 'view') {
					    				$route = ($model->payor_type == 'C') ? '/receipt-contractor' : '/receipt-member';
					    				$url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model->id]);
					    				return $url;
					    			}
				},
    			 
    			'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
    	],
    ]); ?>



</div>
<?= $this->render('../partials/_modal') ?>