<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\models\member\ClassCode;

/* @var $this yii\web\View */
/* @var $hoursProvider \yii\data\ActiveDataProvider */
/* @var $class string */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $id string */

// Member Class
$controller = 'member-class';
?>

<div class="form-group">
    <?php if (($class == ClassCode::CLASS_APPRENTICE) || ($class == ClassCode::CLASS_HANDLER)): ?>
    <div class="pull-right">
        <?php
        /** @noinspection PhpUnhandledExceptionInspection */
        /*
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
                        Html::button('<i class="glyphicon glyphicon-calendar"></i>&nbsp;View Timesheets',
                            ['value' => Url::to(["*", 'member_id'  => $id]),
                                'id' => 'timesheetButton',
                                'class' => 'btn btn-default btn-modal',
                                'data-title' => 'Timesheets',
                                'title' => 'View Timesheets',
                            ])
                ],
                'summary' => '',
                'showPageSummary' => true,
                'columns' => [
                    'work_process',
                    [
                        'attribute' => 'hours',
                        'hAlign' => 'right',
                        'format' => ['decimal', 2],
                        'pageSummary' => true,
                    ],
                ],
            ]);
        */
        ?>
    </div>
    <?php endif; ?>
    <div class="pull-left">
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
                        'label' => 'Percent',
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
                        'class' => \yii\grid\ActionColumn::className(),
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
</div>
