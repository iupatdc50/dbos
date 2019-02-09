<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\helpers\OptionHelper;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\receipt */
/* @var $membProvider yii\data\ActiveDataProvider */
/* @var $searchMemb app\models\accounting\AllocatedMemberSearch */

$void_banner = ($model->void == OptionHelper::TF_TRUE) ? ' <span class="lbl-danger">** VOID **</span>' : '';

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
    		'id' => 'member-grid',
    		'dataProvider' => $membProvider,
        	'filterModel' => $searchMemb,
 			'filterRowOptions'=>['class'=>'filter-row'],
			'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'<i class="glyphicon glyphicon-user"></i>&nbsp;Allocated to Members',
		        'before' => false,
		        'after' => false,
		    ],
    		
    		'rowOptions' => function($model) {
	    		$css = ['verticalAlign' => 'middle'];
	    		if(!isset($model->member->currentStatus) || ($model->member->currentStatus->member_status == 'U'))
	    			$css['class'] = 'warning';
	    		return $css;
    		},
    		
    		
			'columns' => [
					[
							'class'=>'kartik\grid\ExpandRowColumn',
							'width'=>'50px',
							'value'=>function (/** @noinspection PhpUnusedParameterInspection */
                                $model, $key, $index, $column) {
										return GridView::ROW_COLLAPSED;
									 },
							'detailUrl'=> Yii::$app->urlManager->createUrl(['allocation/summary-ajax']),
							'headerOptions'=>['class'=>'kartik-sheet-style'],
    						'expandOneOnly'=>true,
				],
	        	[
	        	        'class' => 'kartik\grid\DataColumn',
	        			'attribute' => 'classification',
	        			'value' => 'member.classification.classification',
	        			'label' => 'Class',
	        			'width' => '5px',
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
        				'attribute' => 'reportId', 
        				'value' => 'member.report_id',
        				
    			],
				[
                        'class' => 'kartik\grid\DataColumn',
						'attribute' => 'totalAllocation',
						'format' => ['decimal', 2],
						'hAlign' => 'right',
    			],
				
			],
	]);

										
