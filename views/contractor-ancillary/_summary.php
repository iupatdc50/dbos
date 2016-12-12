<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$controller = 'contractor-ancillary';
echo GridView::widget([
		'dataProvider' => $dataProvider,
		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'<i class="glyphicon glyphicon-knight"></i>&nbsp;Ancillaries',
				'before' => false,
		        'after' => false,
		        'footer' => false,
		],
		'columns' => [
				[
						'attribute' => 'agreement_type',
						'value' => 'agreementType.descrip',
				],
				'signed_dt:date',
				'term_dt:date',
				[
						'attribute' => 'showPdf',
						'label' => 'Doc',
						'format' => 'raw',
						
						'value' => function($model) {
							return (isset($model->doc_id)) ?
							    Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show original agreement']),
									$model->imageUrl, ['target' => '_blank']) : '';
						},
				],
				[
					'class' => 	'kartik\grid\ActionColumn',
					'visible' => Yii::$app->user->can('updateContractor'),
					'controller' => $controller,
					'template' => '{update}{delete}',
					'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
						['value' => Url::to(["/{$controller}/create", 'relation_id'  => $id]),
						'id' => 'registrationCreateButton',
						'class' => 'btn btn-default btn-modal btn-embedded',
						'data-title' => 'Ancillary',
					]),
				],
		],
]);
