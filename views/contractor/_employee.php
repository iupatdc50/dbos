<?php
use kartik\grid\GridView;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $provider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\employment\EmploymentSearch */
/* @var $license_nbr string */

Pjax::begin ( [ 
		'id' => 'current-employees',
		'enablePushState' => false 
] );
/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget ( [
		'id' => 'current-employees',
		'dataProvider' => $provider,
		'filterModel' => $searchModel,
		'panel' => [ 
				'type' => GridView::TYPE_DEFAULT,
				'heading' => 'Current Employees',
				'before' => '',
				'after' => false 
		],
		'toolbar' => [
		    'content' =>
                Html::a(
                    '<i class="glyphicon glyphicon-print"></i>&nbsp;Print 9a Cards Report',
                    ['/employment/print9a', 'license_nbr' => $license_nbr],
                    ['class' => 'btn btn-default', 'target' => '_blank', 'data-pjax'=>"0"])
        ],
		'columns' => [
                [
                    'class' => 'kartik\grid\ExpandRowColumn',
                    'width' => '50px',
                    'value' => function () {
                        return GridView::ROW_COLLAPSED;
                    },
                    'detailUrl' => Yii::$app->urlManager->createUrl([
                        'employment-document/summary-json',
                    ]),
                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                    'expandOneOnly' => true,
                ],
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
						'attribute' => 'status',
						'value' => 'member.currentStatus.status.descrip',
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
		            			'size' => Select2::SMALL,
		            			'hideSearch' => true,
		            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
		            	],
				] 
		] 
] );
Pjax::end ();
