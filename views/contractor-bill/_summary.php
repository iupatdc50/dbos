<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$controller = 'contractor-bill';

// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'generated-history', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
        'id' => 'generated-history',
		'dataProvider' => $dataProvider,
		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'Generated Remit Templates',
				'before' => false,
		        'after' => false,
//		        'footer' => false,
		],
		'columns' => [
		        [
		            'attribute' => 'created_at',
                    'format' => 'raw',
                    'value' => function($model) {
                        return Html::encode(date('m/d/Y h:i a', $model->created_at));
                    },
                ],
				'lob_cd',
				[
				    'attribute' => 'employees',
                    'label' => 'Billed',
                ],
				'remarks',
				[
						'attribute' => 'created_by',
						'value' => 'creator.username',
				],
				[
					'class' => 	'kartik\grid\ActionColumn',
					'visible' => Yii::$app->user->can('createInvoice'),
					'controller' => $controller,
					'template' => '{delete}',
                    'header' => false,
				],
		],
]);

Pjax::end();