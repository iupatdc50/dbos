<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $heading string */
/* @var $expires bool */
/* @var $relation_id string */

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
    'format' => ['date', 'php:m/Y'],
    'label' => 'Expires',
]] : [];

$action_column = [
    [
        'class' => \yii\grid\ActionColumn::className(),
        'controller' => $controller,
        'template' => '{delete}',

        'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
            'value' => Url::to(["/{$controller}/create", 'relation_id' => $relation_id]),
            'id' => 'credentialCreateButton',
            'class' => 'btn btn-default btn-modal btn-embedded',
            'data-title' => 'Assessment',
        ]),
        'visible' => Yii::$app->user->can('updateReceipt'),

    ],
];


try {
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
        'columns' => array_merge($base_columns, $expires_column, $action_column),

    ]);
} catch (Exception $e) {
}



