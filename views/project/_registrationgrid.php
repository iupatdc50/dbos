<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$awarded = '<span class="glyphicon glyphicon-certificate text-success"></span>';
$pending = '<span class="glyphicon glyphicon-nothing text-danger"></span>';

$baseColumns = [
		[
			'class'=>'kartik\grid\RadioColumn',
		    'width'=>'36px',
			'rowHighlight' => true,
			'rowSelectedClass' => GridView::TYPE_SUCCESS,
		],
		[
			'class'=>'kartik\grid\BooleanColumn',
			'attribute' => 'awarded',
			'value' => function($data) {
							return isset($data->isAwarded);
				       },
			'falseIcon' => $pending,
    		'trueIcon'	=> $awarded,
		],
		[
			'attribute' => 'bidder',
			'format' => 'raw',
			'value' => function($data) {
				    		$label = $data->biddingContractor->contractor;
				    		return Html::a($label, ['contractor/view', 'id' => $data->bidder]);
					   },
			'contentOptions' => ['style' => 'white-space: nowrap;'],
		],
		'bid_dt:date',
];

$actionColumns = [
		[
			'attribute' => 'showPdf',
			'label' => 'Doc',
			'format' => 'raw',
			'value' => function($model) {
							return (isset($model->doc_id)) ?
								Html::a(Html::tag('span', '', [
										'class' => 'glyphicon glyphicon-paperclip', 
										'title' => 'Show original agreement',
								]), $model->imageUrl, ['target' => '_blank']) : 
								'';
						},
			'contentOptions' => ['style' => 'white-space: nowrap;'],
		],
		[
			'class' => 	'kartik\grid\ActionColumn',
			'visible' => Yii::$app->user->can('manageProject'),
			'controller' => $controller,
			'template' => '{update}{delete}',
			'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
					'value' => Url::to(["/{$controller}/create" . ($is_maint ? '-maint' : ''), 'relation_id'  => $model->project_id]),
					'id' => 'registrationCreateButton',
					'class' => 'btn btn-default btn-modal btn-embedded',
					'data-title' => 'Registration',
			]),
		],
];

?>



<?= GridView::widget([
		'dataProvider' => $registrationProvider,
		'id' => 'registration-grid',
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'<i class="glyphicon glyphicon-user"></i>&nbsp;Registrations',
				// workaround to prevent 1 in the before section
				'before' => (Yii::$app->user->can('manageProject')) ? '' : false,
				'after' => false,
				'footer' => false,
		 ],
		'summaryOptions' => ['id' => 'reg-summary'],
		'toolbar' => [
			'options' => ['class' => 'pull-left'],
			'content' => Html::button('<i class="glyphicon glyphicon-certificate"></i>&nbsp;Award', [
					'value' => Url::to(['/awarded-bid/award', 'project_id'  => $model->project_id]),
					'id' => 'awardButton',
					'class' => 'btn btn-default btn-success btn-award',
					'data-title' => 'Award',
			]),
		],
		'columns' => array_merge($baseColumns, $specialColumns, $actionColumns),
]); ?>
