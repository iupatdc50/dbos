<?php

use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
/* @var $id string member ID */

// Member Class
$controller = 'member-class';

// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'class-history', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
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

Pjax::end();
