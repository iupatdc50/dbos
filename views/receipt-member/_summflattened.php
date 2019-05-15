<?php

use kartik\grid\GridView;
use kartik\helpers\Html;
use yii\data\SqlDataProvider;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $sqlProvider SqlDataProvider */
/* @var $typesSubmitted array fee types submittted */

$baseColumns = [
    [
        'attribute' => 'id',
        'label' => 'Nbr',
    ],
    [
        'attribute' => 'received_dt',
        'format' => ['date', 'php:n/j/Y'],
        'label' => 'Received',
    ],
    'payor',
];

$feeColumns = [];
foreach ($typesSubmitted as $typeSubm) {
    $feeColumns[] = [
        'attribute' => $typeSubm['fee_type'],
        'header' => $typeSubm['fee_type'],
        'class' => 'kartik\grid\DataColumn',
        'hAlign' => 'right',
        'vAlign' => 'middle',
        'format' => ['decimal', 2],
    ];
}

$aftColumns  = [
        [
            'attribute' => 'total',
            'class' => 'kartik\grid\DataColumn',
            'hAlign' => 'right',
            'vAlign' => 'middle',
            'contentOptions' => ['class' => 'grid-rowtotal'],
            'format' => ['decimal', 2],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['style' => 'white-space: nowrap;'],
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                        'title' => 'View',
                        'target' => '_blank',
                        'data-pjax'=>"0"
                    ]);
                },
            ],
            'urlCreator' => function ($action, $model) {
                if ($action === 'view') {
                    $route = ($model['payor_type'] == 'M') ? '/receipt-member' : '/receipt-contractor';
                    $url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model['id']]);
                    return $url;
                }
                return null;
            },
        ],
];

?>
<div id="receipt-popup">

<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'receipt-grid', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
        'id' => 'receipt-grid',
        'dataProvider' => $sqlProvider,
        'pjax' => false,
        'resizableColumns' => false,
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'Member Receipts',
            'before' => false,
            'after' => false,
        ],
        'columns' => array_merge($baseColumns, $feeColumns, $aftColumns),
        //		'showPageSummary' => true,
    ]);


?>
</div>
<?php

Pjax::end();
