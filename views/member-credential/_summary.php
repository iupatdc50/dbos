<?php

use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $heading string */
/* @var $expires bool */
/* @var $relation_id string */
/* @var $catg string */

$controller = 'member-credential';

$base_columns = [
    'credential',
    [
        'attribute' => 'complete_dt',
        'format' => 'date',
        'label' => 'Completed',
    ],
];

$expires_column = $expires ? [[
    'attribute' => 'expire_dt',
    'format' => ['date'],
    'label' => 'Expires',
]] : [];

$scheduled_column = [[
    'attribute' => 'schedule_dt',
    'format' => ['date'],
    'label' => 'Scheduled',
]];

$action_column = [
    [
        'class' => ActionColumn::className(),
        'controller' => $controller,
        'template' => '{unschedule} {delete}',

        'buttons' => [
            'unschedule' => function ($url, $model) {
                $eraser = null;
                if(isset($model->schedule_dt))
                    $eraser = Html::a('<span class="glyphicon glyphicon-erase"></span>', $url, [
                        'title' => 'Clear schedule',
                        'data-method' => 'post',
                        'data-confirm' => 'Clear schedule for this item?',
                    ]);
                return $eraser;
            },
            'delete' => function ($url, $model) {
                $delete = null;
                if (isset($model->complete_dt))
                    $delete =  Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'title' => Yii::t('app', 'Delete completed credential'),
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this completed credential?',
                            'method' => 'post',
                        ],
                    ]);
                return $delete;
            }
        ],

        'urlCreator' => function($action, $model) {
            if ($action === 'unschedule') {
                $url = Yii::$app->urlManager->createUrl([
                    '/member-scheduled/clear',
                    'member_id' => $model->member_id,
                    'credential_id' => $model->credential_id
                ]);
                return $url;
            } elseif ($action === 'delete') {
                $url = '/member-credential/delete?id=' . $model->id;
                return $url;
            }
            return null;
        },

        'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
            'value' => Url::to(["/{$controller}/create", 'member_id' => $relation_id, 'catg' => $catg]),
            'id' => $catg . 'CreateButton',
            'class' => 'btn btn-default btn-modal btn-embedded',
            'title' => 'Add a completed credential',
            'data-title' => 'Credential',
        ])
        . ' ' . Html::button('<i class="glyphicon glyphicon-time"></i>', [
                'value' => Url::to(["/member-scheduled/create", 'member_id' => $relation_id, 'catg' => $catg]),
                'id' => $catg . 'SchedButton',
                'class' => 'btn btn-default btn-modal btn-compliance',
                'title' => 'Schedule a credential item',
                'data-title' => 'Schedule',
            ]),
        'visible' => Yii::$app->user->can('manageTraining'),

    ],
];


/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
        'id' => 'credential-grid',
        'dataProvider' => $dataProvider,
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => $heading,
            'before' => false,
            'after' => false,
            'footer' => false,
        ],
        'summary' => '',
        'rowOptions' => function($model) {
            if(isset($model->expire_dt) && ($model->expire_dt < date('Y-m-d'))) {
                $css['class'] = 'danger';
                return $css;
            }
            return null;
        },
        'columns' => array_merge($base_columns, $expires_column, $scheduled_column, $action_column),

    ]);




