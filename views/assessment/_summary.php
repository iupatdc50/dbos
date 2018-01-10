<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $dataProvider yii\data\ActiveDataProvider */

$controller = 'assessment';

try {
    echo GridView::widget([
        'id' => 'assessment-grid',
        'dataProvider' => $dataProvider,
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'Assessments',
            'before' => false,
            'after' => false,
            'footer' => false,
        ],
        'columns' => [
            [
                'class' => \yii\grid\ActionColumn::className(),
                'template' => '{details}',
                'buttons' => [
                    'details' => function ($url, $model, $key) {
                        return Html::button('<i class="glyphicon glyphicon-new-window"></i>', [
                            'value' => $url,
                            'id' => 'detailButton' . $model->id,
                            'class' => 'btn btn-default btn-detail btn-modal',
                            'data-title' => 'Detail',
                            'title' => 'Show details',
                        ]);
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'details')
                        return Yii::$app->urlManager->createUrl(['/assessment/detail-ajax', 'id' => $model->id]);
                },
            ],
            /* This does not work when embedded in accordion
            [
                    'class'=>'kartik\grid\ExpandRowColumn',
                    'width'=>'50px',
                    'value'=>function ($model, $key, $index, $column) {
                        return GridView::ROW_COLLAPSED;
                    },
                    'detailUrl'=> Yii::$app->urlManager->createUrl(['/assessment/detail-ajax']),
                    'headerOptions'=>['class'=>'kartik-sheet-style'],
                    'expandOneOnly'=>true,
            ],
            */

            'fee_type',
            [
                'attribute' => 'assessment_dt',
                'format' => 'date',
                'label' => 'Date',
            ],
            [
                'attribute' => 'assessment_amt',
                'hAlign' => 'right',
            ],
            [
                'attribute' => 'totalAllocated',
                'value' => function ($data) {
                    return (isset($data->totalAllocated)) ? $data->totalAllocated : 0.00;
                },
                'format' => ['decimal', 2],
                'hAlign' => 'right',
                'label' => 'Allocated',
            ],

            'purpose',
            [
                'class' => \yii\grid\ActionColumn::className(),
                'controller' => $controller,
                'template' => '{waive} {delete}',
                'buttons' => [
                    'waive' => function ($url, $model) {
                        return Html::button('<i class="glyphicon glyphicon-ok-circle"></i>', [
                            'value' => $url,
                            'id' => 'waiveButton' . $model->id,
                            'class' => 'btn btn-default btn-detail btn-modal',
                            'data-title' => 'Waive',
                            'title' => Yii::t('app', 'Waive Assessment'),
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => Yii::t('app', 'Delete'),
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ]);
                    }
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action == 'waive') {
                        $url = '/assessment/waive?id=' . $model->id;
                        return $url;
                    } elseif ($action === 'delete') {
                        $url = '/assessment/delete?id=' . $model->id;
                        return $url;
                    }
                },

                'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
                    'value' => Url::to(["/{$controller}/create", 'relation_id' => $relation_id]),
                    'id' => 'assessmentCreateButton',
                    'class' => 'btn btn-default btn-modal btn-embedded',
                    'data-title' => 'Assessment',
                ]),
                'visible' => Yii::$app->user->can('updateReceipt'),

            ],
        ],
    ]);
} catch (Exception $e) {
}

?>


