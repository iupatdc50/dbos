<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
// use kartik\detail\DetailView;
use kartik\grid\GridView;


/* @var $this yii\web\View */
/* @var $model app\models\project\jtp\Project */

$this->title = $model->project_nm;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('../project/_viewtoolbar', ['model' => $model]); ?>

<table class="hundred-pct table">
<tr><td class="sixty-pct datatop">
    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
    	'attributes' => [
            [
            	'attribute' => 'project_status', 
            	'value' => Html::encode($model->statusText),
            	'contentOptions' => $model->project_status == 'A' ? ['class' => 'success'] : ($model->project_status == 'X' ? ['class' => 'danger'] : ['class' => 'default']),
            ],
            'project_id',
            'addressTexts:ntext',
    		'general_contractor',
            ['attribute' => 'agreement_type', 'value' => Html::encode($model->agreementType->descrip)],
            [
            	'label' => 'Hold Amount ($)',
            	'value' => isset($model->holdAmount) ? $model->holdAmount->hold_amt : null,
            	'format' => ['decimal', 2],
            ],
            [
            	'attribute' => 'disposition', 
            	'format' => 'raw',
            	'value' => $model->disposition == 'A' ? 
            		'<span class="text-success">Approved</span>' :
            		'<span class="text-danger">Denied</span>',
            ],
            [
            	'label' => 'Start Date',
            	'format' => 'date',
            	'value' => isset($model->awarded) ? $model->awarded->start_dt : null,
            ],
            'close_dt:date',
        ],
    ]) ?>
    
    <?php 
    $specialColumns = [
    		[
    			'attribute' => 'estimated_hrs',
    			'label' => 'Hours',
    			'hAlign' => 'right',
    			'format' => ['decimal', 0],
    		],
    		[
    			'attribute' => 'subsidy_rate',
    			'label' => 'Rate',
    			'hAlign' => 'right',
    			'format' => ['decimal', 2],
    		],
    ];

    echo $this->render('../project/_registrationgrid', [
    		'registrationProvider' => $registrationProvider,
    		'model' => $model,
    		'is_maint' => false,
    		'controller' => 'registration-jtp',
    		'specialColumns' => $specialColumns,
     ]);
        
    
    ?>
    
    <?= GridView::widget([
    	'dataProvider' => $paymentProvider,
    	'showPageSummary' => true,
		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'<i class="glyphicon glyphicon-usd"></i>&nbsp;Subsidies Paid',
		        'before' => false,
		        'after' => false,
		        'footer' => false,
		],
		'columns' => [
        		'payment_dt:date',
        		[
        			'attribute' => 'paid_amt',
        			'hAlign' => 'right',
        			'format' => ['decimal', 2],
        			'pageSummary' => true,
        		],
        		[
        			'attribute' => 'actual_hrs',
        			'hAlign' => 'right',
        			'format' => ['decimal', 2],
        			'pageSummary' => true,
        		],
        		[
        				'class' => 	'kartik\grid\ActionColumn',
        				'visible' => Yii::$app->user->can('manageProject'),
        				'controller' => '/payment',
        				'template' => '{delete}',
        				'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
							['value' => Url::to(["/payment/create", 'relation_id'  => $model->project_id]),
							'id' => 'paymentButton',
							'class' => 'btn btn-default btn-modal btn-embedded',
							'data-title' => 'Subsidy Payment',
						]),
        		],
        		
        	]
    ]); ?>    	
							
</td><td class="forty-pct datatop">

  <?=  $this->render('../partials/_journal', ['model' => $model, 'noteModel' => $noteModel]) ?>
  
</td></tr></table>
    
</div>
<?= $this->render('../partials/_modal') ?>