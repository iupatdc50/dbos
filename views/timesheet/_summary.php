<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\member\ClassCode;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $hoursProvider ActiveDataProvider */
/* @var $class string */
/* @var $id string member ID */

// timesheet
$controller = 'timesheet';

if (($class == ClassCode::CLASS_APPRENTICE) || ($class == ClassCode::CLASS_HANDLER))
    /** @noinspection PhpUnhandledExceptionInspection */
    echo GridView::widget([
        'id' => 'hours-summary',
        'dataProvider' => $hoursProvider,
        'pjax' => false,
        'panel' => [
            'type'=>GridView::TYPE_DEFAULT,
            'heading'=>'Hours',
            'before' => (Yii::$app->user->can('manageTraining')) ? '' : false,
            'after' => false,
            'footer' => false,

        ],
        'toolbar' => [
            'content' =>
                Html::a('<i class="glyphicon glyphicon-calendar"></i>&nbsp;DPR Timesheets',
                    ["/{$controller}/index", 'member_id' => $id],
                    ['class' => 'btn btn-default']) . '' .
                Html::button('<i class="glyphicon glyphicon-hdd"></i>',
                    ['value' => Url::to([$controller . '/archive', 'member_id'  => $id]),
                        'id' => 'archiveButton',
                        'class' => 'btn btn-default btn-modal',
                        'data-title' => 'Archive',
                        'title' => 'Archive current timesheets',
                        'disabled' => !(Yii::$app->user->can('archiveTimesheets')),
                    ]) . '' .
                Html::button('<i class="glyphicon glyphicon-share-alt"></i>',
                    ['value' => Url::to([$controller . '/restore', 'member_id'  => $id]),
                        'id' => 'archiveButton',
                        'class' => 'btn btn-default btn-modal',
                        'data-title' => 'Archive',
                        'title' => 'Restore trade timesheets from archive',
                        'disabled' => !(Yii::$app->user->can('archiveTimesheets')),
                    ])
        ],
        'summary' => '',
        'showPageSummary' => true,
        'columns' => [
            [
                'class'=>'kartik\grid\BooleanColumn',
                'falseIcon' => '<span></span>',
                'attribute' => 'target',
                'label' => false,
                'value' => function($model) {
                    return ($model->target > $model->hours) ? false : true;
                },
            ],
            [
                'attribute' => 'work_process',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'hours',
                'hAlign' => 'right',
                'format' => ['decimal', 2],
                'pageSummary' => true,
            ],
        ],
    ]);

else if(Yii::$app->user->can('manageTraining'))
    echo Html::a('<i class="glyphicon glyphicon-calendar"></i>&nbsp;DPR Timesheets',
        ["/{$controller}/index", 'member_id' => $id],
        ['class' => 'btn btn-default', 'target' => '_blank']);
