<?php

use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\models\member\ClassCode;

/* @var $this yii\web\View */
/* @var $hoursProvider ActiveDataProvider */
/* @var $class string */
/* @var $dataProvider ActiveDataProvider */
/* @var $id string */

// Member Class
$controller = 'member-class';
?>

<div class="form-group">

    <div class="leftside fifty-pct">
        <?php
        // 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
        Pjax::begin(['id' => 'class-history', 'enablePushState' => false]);

        try {
            echo GridView::widget([
                'id' => 'class-history',
                'pjax' => false,
                'dataProvider' => $dataProvider,
                'panel' => [
                    'type'=>GridView::TYPE_DEFAULT,
                    'heading'=>'Class History',
                    'before' =>  false,
                    'after' => false,

                ],
                'columns' => [
                    [
                        'attribute' => 'mClass',
                        'label' => 'Class',
                        'value' => 'mClass.descrip',
                        'contentOptions' => ['style' => 'white-space: nowrap;'],
                    ],
                    'effective_dt:date',
                    //				'end_dt:date',
                    [
                        'attribute' => 'rClass',
                        'label' => 'Rate',
                        'value' => 'rClass.descrip',
                    ],
                    [
                        'class' => 'kartik\grid\DataColumn',
                        'attribute' => 'wage_percent',
                        'label' => '%',
                        'hAlign' => 'right',
                    ],
                    [
                        'attribute' => 'showPdf',
                        'label' => 'Doc',
                        'format' => 'raw',
                        'value' => function($model) {
                            return (isset($model->doc_id)) ?
                                Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show change notice']),
                                    $model->imageUrl, ['target' => '_blank', 'data-pjax'=>"0"]) : '';
                        },
                    ],
                    [
                        'class' => ActionColumn::className(),
                        'controller' => $controller,
                        'template' => '{delete}',
                        'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
                            'value' => Url::to(["/{$controller}/create", 'relation_id' => $id]),
                            'id' => 'classCreateButton',
                            'class' => 'btn btn-default btn-modal btn-embedded',
                            'data-title' => 'Member Class',
                        ]),
                        'visible' => Yii::$app->user->can('updateDemo'),
                    ],
                ],
            ]);
        } catch (Exception $e) {
        }

        Pjax::end();
        ?>
    </div>
    <?php if(Yii::$app->user->can('manageTraining')): ?>
    <?php if (($class == ClassCode::CLASS_APPRENTICE) || ($class == ClassCode::CLASS_HANDLER)): ?>
    <div class="rightside fortyfive-pct">
        <?php
        /** @noinspection PhpUnhandledExceptionInspection */

        echo GridView::widget([
                'id' => 'hours-summary',
                'dataProvider' => $hoursProvider,
                'panel' => [
                    'type'=>GridView::TYPE_DEFAULT,
                    'heading'=>'Hours',
                    'before' => (Yii::$app->user->can('manageTraining')) ? '' : false,
                    'after' => false,
                    'footer' => false,

                ],
                'toolbar' => [
                    'content' =>
                      Html::a('<i class="glyphicon glyphicon-calendar"></i>&nbsp;Timesheets',
                        ['/timesheet/index', 'member_id' => $id],
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
//                        'width' => '50px',
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

        ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
