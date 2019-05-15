<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */

?>

<div id="dues-popup">


<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'dues-grid', 'enablePushState' => false]);


/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
		'id' => 'dues-grid',
		'dataProvider' => $dataProvider,
		'summary' => '',
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'Dues Receipts',
		    	'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
		        'receipt_id',
		        [
                        'attribute' => 'received_dt',
                        'format' => 'date',
                        'label' => 'Received',
                ],
				[
						'class' => '\kartik\grid\DataColumn',
						'attribute' => 'amt',
						'format' => ['decimal', 2],
						'hAlign' => 'right',
		                'label' => 'Dues',
				],
				[
                        'class' => '\kartik\grid\DataColumn',
						'attribute' => 'months',
						'hAlign' => 'right',
				],
				[
						'attribute' => 'paid_thru_dt',
						'format' => 'date',
						'label' => 'Paid Thru',
				],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' => ['style' => 'white-space: nowrap;'],
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url) {
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', null, ['title' => 'View', 'onclick' => "window.open('{$url}');"]);
                        },
                    ],
                    'urlCreator' => function ($action, $model) {
                        if ($action === 'view') {
                            $route = ($model->receipt->payor_type == 'M') ? '/receipt-member' : '/receipt-contractor';
                            $url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model->receipt_id]);
                            return $url;
                        }
                        return null;
                    },
                ],
		],
]);

?>
</div>

<?php

Pjax::end();
