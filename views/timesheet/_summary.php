<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\member\ClassCode;

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
                ['class' => 'btn btn-default'])
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
