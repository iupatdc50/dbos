<?php

use app\models\member\SubscriptionEvent;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\helpers\OptionHelper;
use app\models\accounting\Receipt;

/* @var $this yii\web\View */
/* @var $receiptSearchModel app\models\accounting\ReceiptSearch */
/* @var $receiptProvider yii\data\ActiveDataProvider */
/* @var $mine_only boolean  */
/* @var $payorPicklist array */
/* @var $eventSearchModel app\models\member\SubscriptionEventSearch */
/* @var $eventProvider yii\data\ActiveDataProvider */
/* @var $statusPicklist array */

$this->title = 'Accounting';
$this->params['breadcrumbs'][] = $this->title;

$show_class = $mine_only ? 'glyphicon glyphicon-expand' : 'glyphicon glyphicon-user';
$show_label = $mine_only ? 'All' : 'Mine Only';
$toggle_mine_only = !$mine_only;


?>

<div class="accounting-index">

    <table class="hundred-pct">
        <tr>
            <td class="fiftyfive-pct pad-six">
                <?= /** @noinspection PhpUnhandledExceptionInspection */
                GridView::widget([
                    'dataProvider' => $receiptProvider,
                    'filterModel' => $receiptSearchModel,
                    'filterRowOptions'=>['class'=>'filter-row'],
                    'panel'=>[
                        'type'=>GridView::TYPE_PRIMARY,
                        'heading'=> 'Receipts',
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
                    'rowOptions' => function(Receipt $model) {
                        $css = ['verticalAlign' => 'middle'];
                        if ($model->void == OptionHelper::TF_TRUE)
                            $css['class'] = 'text-muted';
                        elseif ($model->isUpdating())
                            $css['class'] = 'warning';

                        return $css;
                    },
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'label' => 'Nbr',
                            'contentOptions' => ['style' => 'white-space: nowrap;'],
                            'format' => 'raw',
                            'value' => function(Receipt $model) {
                                return Html::a(($model->isUpdating()) ? $model->id . ' [** NOT POSTED **]' : $model->id,
                                    Yii::$app->urlManager->createUrl(['/receipt-' . $model->urlQual . '/view', 'id' => $model->id])
                                );
                            },
                        ],
                        'lob_cd',
                        [
                            'attribute' => 'received_dt',
                            'format' => 'date',
                            'label' => 'Received',
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'payor_type_filter',
                            'width' => '140px',
                            'value' => 'payorText',
                            'label' => 'Type',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => array_merge(["" => ""], $payorPicklist),
                            'filterWidgetOptions' => [
                                'size' => Select2::SMALL,
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
                        /*  Remove for performance reasons (does not use an index with eager load)
                        [
                                'attribute' => 'feeTypes',
                                'value' => 'feeTypeTexts',
                                'format'  => 'ntext',
                                'contentOptions' => ['style' => 'white-space: nowrap;'],
                        ],
                        */
                    ],
                ]); ?>
            </td>
            <td class="datatop pad-six">
                <?= /** @noinspection PhpUnhandledExceptionInspection */
                GridView::widget([
                    'dataProvider' => $eventProvider,
                    'filterModel' => $eventSearchModel,
                    'filterRowOptions'=>['class'=>'filter-row'],
                    'panel'=>[
                        'type'=>GridView::TYPE_INFO,
                        'heading'=> 'Subscription Events',
                        // workaround to prevent 1 in the before section
                        'before' => false,
                        'after' => false,
                    ],
                    'rowOptions' => function(SubscriptionEvent $model) {
                        $css = ['verticalAlign' => 'middle'];
                        if ($model->status == SubscriptionEvent::STATUS_FAILED)
                            if (isset($model->next_attempt))
                                $css['class'] = 'warning';
                            else // final attempt was made
                                $css['class'] = 'danger';

                        return $css;
                    },
                    'columns' => [
                        [
                            'attribute' => 'fullName',
                            'contentOptions' => ['style' => 'white-space: nowrap;'],
                            'format' => 'raw',
                            'value' => function (SubscriptionEvent $model) {
                                return Html::a($model->member->fullName, Yii::$app->urlManager->createUrl(['/member/view', 'id' => $model->member->member_id]));
                            }
                        ],
                        [
                            'attribute' => 'invoice_id',
                            'format' => 'raw',
                            'value' => function(SubscriptionEvent $model) {return '...' . substr($model->invoice_id, -4);}
                        ],
                        [
                            'attribute' => 'created_dt',
                            'format' => 'date',
                            'width' => '140px',
                        ],
                        'charge_amt',
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'status',
                            'width' => '100px',
                            'format' => 'raw',
                            'value' => function (SubscriptionEvent $model) {
                                if ($model->status == SubscriptionEvent::STATUS_FAILED)
                                    return $model->getStatusText();
                                return Html::a($model->getStatusText(), Yii::$app->urlManager->createUrl(['/receipt-member/view', 'id' => $model->receipt_id]));
                            },
                            'label' => 'Status',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => array_merge(["" => ""], $statusPicklist),
                            'filterWidgetOptions' => [
                                'size' => Select2::SMALL,
                                'hideSearch' => true,
                                'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
                            ],
                        ],
                        [
                            'attribute' => 'next_attempt',
                            'format' => 'raw',
                            'value' => function(SubscriptionEvent $model) {
                                if (isset($model->next_attempt))
                                    return date('m/d/Y', $model->next_attempt);
                                if ($model->status == SubscriptionEvent::STATUS_FAILED)
                                    return 'Final';
                                return '';
                            },
                        ],
                    ],

                ]);
                ?>
            </td>
        </tr>
    </table>

</div>
<?= $this->render('../partials/_modal') ?>