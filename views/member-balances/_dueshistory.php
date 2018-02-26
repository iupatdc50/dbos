<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

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
                        'view' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => 'View', 'target' => '_blank']);
                        },
                    ],
                    'urlCreator' => function ($action, $model, $key, $index) {
                        if ($action === 'view') {
                            $route = ($model->receipt->payor_type == 'C') ? '/receipt-contractor' : '/receipt-member';
                            $url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model->receipt_id]);
                            return $url;
                        }
                    },
                ],
		],
]);

?>
</div>

<?php

Pjax::end();
