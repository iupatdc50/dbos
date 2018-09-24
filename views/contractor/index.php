<?php

use yii\helpers\Html;
use yii\helpers\Url;
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
			// workaround to prevent 1 in the before section
			'before' => (Yii::$app->user->can('createContractor')) ? '' : false,
			'after' => false,
		],
		'toolbar' => ['content' => Html::a('Create Contractor', ['create'], ['class' => 'btn btn-success'])],
        'rowOptions' => function($model) {
        					if($model->is_active == 'F') {
        						return ['class' => 'text-muted'];
        					}
    					},
        'columns' => [
        	[
        		'class' => 'yii\grid\ActionColumn',
        		'visible' => Yii::$app->user->can('createInvoice'),
            	'template' => '{export}',
            	'buttons' => [
        			'export' => function ($url, $model) {
            						return Html::button('<i  class="glyphicon glyphicon-export"></i>',
            								[
            										'value' => Url::to(['/contractor/create-remit', 'id' => $model->license_nbr]),
            										'id' => 'remitCreateButton',
            										'class' => 'btn btn-default btn-modal btn-embedded',
            										'title' => 'Generate Remittance Template',
            										'data-title' => 'Remit',
            								]);
        		        		},
        		        		
        				
        		],
        	],
        		
        	[
        	    'class' => 'kartik\grid\DataColumn',
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
    			'label' => 'Trade(s)',
    		],
            'license_nbr',
            [
            	'attribute' => 'contractor',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
				'format' => 'raw',
				'value' => function($model) {
					return Html::a(Html::encode($model->contractor), '/contractor/view?id=' . $model->license_nbr);
				},
            ],
        	[
            	'attribute' => 'contact_nm',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
            [
                'attribute' => 'email',
                'format' => 'email',
                'value' => 'contactEmail.email',
            ],
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
            	'visible' => Yii::$app->user->can('updateContractor'),
            ],
        ],
    ]); ?>

</div>
<?= $this->render('../partials/_modal') ?>