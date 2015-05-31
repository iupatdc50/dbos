<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

echo GridView::widget([
		'dataProvider' => $dataProvider,
		'floatHeader' => true,
		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'Active Projects',
		        'before' => false,
		        'after' => false,
		],
		'columns' => [
				[
						'attribute' => 'project',
    					'format' => 'raw',
    					'value' => function($data) {
    						$label = $data->project->project_nm;
    						$type = strtolower($data->project->agreement_type);
    						return Html::a($label, ["project-{$type}/view", 'id' => $data->project_id]);
    					},
				],
				[
						'attribute' => 'type',
						'value' => 'project.agreement_type',
				],
				'bid_dt:date',
				[
						'attribute' => 'start_dt',
						'format' => 'date',
						'value' => 'isAwarded.start_dt',
				],
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
		],
]);
