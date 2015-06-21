<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$controller = 'member-document';
echo GridView::widget([
		'dataProvider' => $dataProvider,
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'<i class="glyphicon glyphicon-folder-close"></i>&nbsp;Documents',
				'class' => 'text-primary',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
				'doc_type',
				[
				'attribute' => 'showPdf',
				'label' => 'Doc',
				'format' => 'raw',
				'value' => function($model) {
					return (isset($model->doc_id)) ?
						Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show document']),
							$model->imageUrl, ['target' => '_blank']) : '';
						},
				],
				[
					'class' => 	'kartik\grid\ActionColumn',
							'controller' => $controller,
							'template' => '{delete}',
						'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
							['value' => Url::to(["/{$controller}/create", 'relation_id'  => $id]),
													'id' => 'documentCreateButton',
							'class' => 'btn btn-default btn-modal btn-embedded',
							'data-title' => 'Document',
						]),
				],
		],
]);
				