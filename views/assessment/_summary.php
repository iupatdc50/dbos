<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

$controller = 'assessment';

echo GridView::widget([
		'id' => 'assessment-grid',
		'dataProvider' => $dataProvider,
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'Open Assessments',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
				[
						'class' => \yii\grid\ActionColumn::className(),
						'template' => '{details}',
						'buttons' => [
								'details' => function ($url, $model, $key) {
												return Html::button('<i class="glyphicon glyphicon-new-window"></i>', [
															'value' => $url,
															'id' => 'detailButton' . $model->id,
															'class' => 'btn btn-default btn-detail btn-modal',
															'data-title' => 'Detail',
															'title' => 'Show details',
												]);
								},
						],
						'urlCreator' => function ($action, $model, $key, $index) {
							if ($action === 'details')
								return Yii::$app->urlManager->createUrl(['/assessment/detail-ajax', 'id'  => $model->id]);
						},
				],
						
				'fee_type',
				[
						'attribute' => 'assessment_dt',
						'format' => 'date',
						'label' => 'Date',
				],
				[
						'attribute' => 'assessment_amt',
						'hAlign' => 'right',
				],
				[
						'attribute' => 'totalAllocated',
						'value' => function($data) {
							return (isset($data->totalAllocated)) ? $data->totalAllocated : 0.00;
						},
						'format' => ['decimal', 2],
						'hAlign' => 'right',
						'label' => 'Allocated',
				],
				
				'purpose',
				[
					'class' => \yii\grid\ActionColumn::className(),
					'controller' => $controller,
					'template' => '{delete}',
						
					'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
								'value' => Url::to(["/{$controller}/create", 'relation_id'  => $relation_id]),
								'id' => 'assessmentCreateButton',
								'class' => 'btn btn-default btn-modal btn-embedded',
								'data-title' => 'Assessment',
					]),
					'visible' =>  Yii::$app->user->can('updateReceipt'),
					
				],
		],
]);

?>


