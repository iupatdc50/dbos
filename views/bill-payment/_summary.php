<?php

use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */

?>

    <div id="payment-panel" class="leftside seventyfive-pct">

<?php

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
    'id' => 'payment-grid',
    'dataProvider' => $dataProvider,
    'panel'=>[
        'type'=>GridView::TYPE_DEFAULT,
        'heading'=>'<i class="glyphicon glyphicon-usd"></i>&nbsp;Payments',
        'class' => 'text-primary',
        'before' => false,
        'after' => false,
        'footer' => false,
    ],
    'columns' => [
        'receipt_id',
        'receipt.received_dt:date',
        'receipt.payor_nm',
        'receipt.received_amt',
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
                    $route = '/receipt-contractor';
                    return Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model['receipt_id']]);
                }
                return null;
            },
        ],
    ],
]);

?>
    </div>
<?php

