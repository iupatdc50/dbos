<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $ modelsSpecialty \yii\db\ActiveQuery */
/* @var $relation_id string */

$controller = 'member-specialty';
echo GridView::widget([
		'dataProvider' => new \yii\data\ActiveDataProvider([
			        		'query' => $modelsSpecialty,
			        		'pagination' => false,
		]),
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'<i class="glyphicon glyphicon-education"></i>&nbsp;Specialties',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
				'specialty',
				[
				'class' => 	'kartik\grid\ActionColumn',
						'controller' => $controller,
						'template' => '{delete}',
						'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
						['value' => Url::to(["/{$controller}/create", 'relation_id'  => $relation_id]),
												'id' => 'specialtyCreateButton',
						'class' => 'btn btn-default btn-modal btn-embedded',
						'data-title' => 'Specialty',
					]),
		],
	],
]);
				