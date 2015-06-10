<?php
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $model yii\data\ActiveDataProvider */
/* @var $searchModel app\models\member\EmploymentSearch */

Pjax::begin ( [ 
		'id' => 'current-employees',
		'enablePushState' => false 
] );
echo GridView::widget ( [ 
		'id' => 'current-employees',
		'dataProvider' => $provider,
		'filterModel' => $searchModel,
		'panel' => [ 
				'type' => GridView::TYPE_DEFAULT,
				'heading' => 'Current Employees',
				'before' => false,
				'after' => false 
		],
		'columns' => [ 
				[ 
						'attribute' => 'fullName',
						'format' => 'raw',
						'value' => function ($data) {
							return Html::a ( Html::encode ( $data->member->fullName ), [ 
									'member/view',
									'id' => $data->member_id 
							] );
						} 
				],
				[ 
						'attribute' => 'is_loaned',
						'label' => 'Special',
						'format' => 'raw',
						'value' => function ($data) {
							return ($data->is_loaned == 'T') ? Html::tag ( 'span', ' On Loan', [ 
									'class' => 'label label-success' 
							] ) : '';
						}, 
		            	'filterType' => GridView::FILTER_SELECT2,
		            	'filter' => ["" => "", "T" => "Loaned"],
		            	'filterWidgetOptions' => [
		            			'size' => \kartik\widgets\Select2::SMALL,
		            			'hideSearch' => true,
		            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
		            	],
				] 
		] 
] );
Pjax::end ();
