<?php

/**
 * Template view of the grid for Project specific columns are passed 
 * and tagged on to the end of the grid before the Action Column
 */

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\grid\GridViewAsset;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $heading {$this->title} from calling view */
/* @var $before string */
/* @var $filterModel yii\db\ActiveRecord */
/* @var $specialColumns array specific to calling view */

$baseColumns = [
    	[
        	'attribute' => 'project_status',
    		'width' => '130px',
    		'value'	=> 'statusText',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => array_merge(["" => ""], OptionHelper::getStatusOptions()),
            'filterWidgetOptions' => [
            	'size' => \kartik\widgets\Select2::SMALL,
            	'hideSearch' => true,
            	'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
            ],
		],
        [
            	'attribute' => 'project_nm',
            	'contentOptions' => ['style' => 'white-space: nowrap; '],
				'format' => 'raw',
				'value' => function($model) {
					return Html::a(Html::encode($model->project_nm), Url::to(['view', 'id' => $model->project_id]));
				},
        ],
        [
            'attribute' => 'general_contractor',
            'contentOptions' => ['style' => 'white-space: nowrap;'],
        ],
        [
        	'attribute' => 'disposition',
        	'width' => '140px',
        	'value' => 'dispText',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => array_merge(["" => ""], OptionHelper::getDispOptions()),
            'filterWidgetOptions' => [
            	'size' => \kartik\widgets\Select2::SMALL,
            	'hideSearch' => true,
            	'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
        	],
        ],
        [
        	'attribute' => 'awarded_contractor',
        	'value' => 'awarded.registration.biddingContractor.contractor',
        	'label' => 'Awarded To',
        	'contentOptions' => ['style' => 'white-space: nowrap;'],
        ],
        		
];

$actionColumn = [
		[
				'class' => 'yii\grid\ActionColumn',
				'contentOptions' => ['style' => 'white-space: nowrap;'],
				'visible' => Yii::$app->user->can('updateProject'),				
        ],
];

?>
    
<?php
try {
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => $heading,
            // workaround to prevent 1 in the before section
            'before' => (Yii::$app->user->can('manageProject')) ? '' : false,
            'after' => isset($before) ? '<p class="pad-six">' . Html::tag('span', $before, ['class' => 'pull-right']) . '</p>' : false,
        ],
        'toolbar' => [
            'content' => Html::a('Create Project', ['create'], ['class' => 'btn btn-success']),
        ],
        'rowOptions' => function ($model) {
            if (!is_null($model->close_dt))
                return ['class' => 'text-muted'];
			elseif ($model->disposition == 'D')
                return ['class' => 'danger text-muted'];
			elseif ($model->disposition == 'U')
                return ['class' => 'warning'];
        },
        'columns' => array_merge($baseColumns, $specialColumns, $actionColumn),
    ]);
} catch (Exception $e) {
} ?>

