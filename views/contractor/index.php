<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\contractor\ContractorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Contractors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contractor-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Contractor', ['create'], ['class' => 'btn btn-success']),
		],
        'rowOptions' => function($model) {
        					if($model->is_active == 'F') {
        						return ['class' => 'text-muted'];
        					}
    					},
        'columns' => [
        	[
        		'class' => 'yii\grid\ActionColumn',
            	'template' => '{export}',
            	'buttons' => [
        			'export' => function ($url, $model) {
        		    				return Html::a('<span class="glyphicon glyphicon-export"></span>', $url, ['title' => 'Generate remittance template', 'data-pjax' => '0']);
        		        		},
        		],
        		'urlCreator' => function ($action, $model, $key, $index) {
        		            		if ($action === 'export') {
        		            			$url = Yii::$app->urlManager->createUrl(['contractor/remit-template', 'id' => $model->license_nbr]);
        		            			return $url;
        		            		}
        						},
        	],
        		
        	[
        		'attribute' => 'is_active',
    			'width' => '110px',
    			'value' => 'statusText',
            	'filterType' => GridView::FILTER_SELECT2,
            	'filter' => array_merge(["" => ""], $searchModel->statusOptions),
            	'filterWidgetOptions' => [
            			'size' => \kartik\widgets\Select2::SMALL,
            			'hideSearch' => true,
            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
            	],
        	],
    		[
    			'attribute' => 'lobs',
    			'value' => 'currentSignatory.lobs',
    			'label' => 'Union(s)',
    		],
            'license_nbr',
            [
            	'attribute' => 'contractor',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
        	[
            	'attribute' => 'contact_nm',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
            'email:email',
            [
            	'attribute' => 'employeeCount',
            	'contentOptions' => function($model) {
			        					if($model->employeeCount == 0) {
			        						return ['class' => 'right zero'];
			        					} else {
			        						return ['class' => 'right'];
			        					}
			    					},
            ],
            [
            	'class' => 'yii\grid\ActionColumn',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
        ],
    ]); ?>

</div>
