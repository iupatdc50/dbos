<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
/* @var $id string   License number */

$controller = 'contractor-signatory';
/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
        'id' => 'contractor-signatory',
		'dataProvider' => $dataProvider,
		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'<i class="glyphicon glyphicon-edit"></i>&nbsp;Signatories',
				'class' => 'text-primary',
				'before' => false,
		        'after' => false,
		        'footer' => false,
		],
		'columns' => [
            [
                'class' => 'kartik\grid\ExpandRowColumn',
                'width' => '50px',
                'value' => function () {
                    return GridView::ROW_COLLAPSED;
                },
                'detailUrl' => Yii::$app->urlManager->createUrl([
                    "{$controller}/history-json",
                ]),
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'expandOneOnly' => true,
            ],
				[
						'attribute' => 'lob',
						'label' => 'Local',
						'value' => 'lob.short_descrip',
				],
				[
						'class'=>'kartik\grid\BooleanColumn',
						'falseIcon' => '<span></span>',
						'attribute' => 'is_pla',
						'label' => 'PLA',
						'value' => function($model) {
							return $model->is_pla == 'T';
						}
				],
				[
						'class'=>'kartik\grid\BooleanColumn',
						'falseIcon' => '<span></span>',
						'attribute' => 'assoc',
						'label' => 'Assoc',
						'value' => function($model) {
							return $model->assoc == 'T';
						}
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
						'data-title' => 'Signatory',
					]),
				],
		],
]);
