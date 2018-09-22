<?php

use kartik\grid\GridView;
use kartik\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $sqlProvider \yii\data\SqlDataProvider */
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
];

$feeColumns = [];
foreach ($typesSubmitted as $typeSubm) {
    if (strlen($typeSubm['fee_type']) == 2)
        $header = $typeSubm['fee_type'];
    else
        $header = ($typeSubm['fee_type'] == 'unallocated_amt') ? 'Unalloc' : 'Helper';
    $feeColumns[] = [
        'attribute' => $typeSubm['fee_type'],
        'header' => $header,
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
                'view' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                        'title' => 'View',
                        'target' => '_blank',
                        'data-pjax'=>"0"
                    ]);
                },
            ],
            'urlCreator' => function ($action, $model, $key, $index) {
                if ($action === 'view') {
                    $route = '/receipt-contractor';
                    $url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model['id']]);
                    return $url;
                }
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
