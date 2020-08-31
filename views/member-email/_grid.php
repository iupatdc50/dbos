<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $modelsEmail ActiveQuery */
/* @var $relation_id string */
/* @var $count integer */

$controller = 'member-email';
/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
		'dataProvider' => new ActiveDataProvider([
			        		'query' => $modelsEmail,
			        		'pagination' => false,
		]),
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'<i class="glyphicon glyphicon-send"></i>&nbsp;Email (1 allowed per member)',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
				'email',
				[
				'class' => 	'kartik\grid\ActionColumn',
						'controller' => $controller,
						'template' => '{delete}',
						'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
						['value' => Url::to(["/{$controller}/create", 'relation_id'  => $relation_id]),
												'id' => 'emailCreateButton',
						'class' => 'btn btn-default btn-modal btn-embedded',
						'data-title' => 'Email',
                        'disabled' => !($count == 0),
					]),
		],
	],
]);
				