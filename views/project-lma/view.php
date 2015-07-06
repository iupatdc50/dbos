<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\detail\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\project\Project */

$this->title = $model->project_nm;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->project_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->project_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?php if($model->project_status == 'A') : ?>
        <?= Html::button('<i class="glyphicon glyphicon-minus-sign"></i>&nbsp;Cancel Project', 
	        			['value' => Url::to(["cancel", 'id'  => $model->project_id]),
            				'id' => 'cancelButton',
            				'class' => 'btn btn-default btn-modal',
            				'data-title' => 'Cancellation',	
        ]) ?>
        <?php endif; ?>
    </p>

<table class="hundred-pct table">
<tr><td class="sixty-pct datatop">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
            	'attribute' => 'project_status', 
            	'value' => Html::encode($model->statusText),
            	'rowOptions' => $model->project_status == 'A' ? ['class' => 'success'] : ($model->project_status == 'X' ? ['class' => 'danger'] : ['class' => 'default']),
            ],
            'project_id',
            'addressTexts:ntext',
    		'general_contractor',
            ['attribute' => 'agreement_type', 'value' => Html::encode($model->agreementType->descrip)],
            [
            	'attribute' => 'disposition', 
            	'format' => 'raw',
            	'value' => $model->disposition == 'A' ? 
            		'<span class="text-success">Approved</span>' :
            		'<span class="text-danger">Denied</span>',
            ],
            [
            	'attribute' => 'is_maint',
            	'format' => 'raw',
            	'label' => 'Job Type',
            	'value' => ($model->is_maint == 'T') ? '<span class="label label-warning">Maintenance</span>'
	        										 : 'Project',
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
    $awarded = '<span class="glyphicon glyphicon-asterisk text-success"></span>';
    $pending = '<span class="glyphicon glyphicon-nothing text-danger"></span>';
    $est_visible = ($model->is_maint == 'F');
    $controller = 'registration-lma';
    ?>
    
    <?= GridView::widget([
    	'dataProvider' => $registrationProvider,
    		'id' => 'registration-grid',
    		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'<i class="glyphicon glyphicon-user"></i>&nbsp;Registrations',
		        'after' => false,
		        'footer' => false,
		    ],
		    'summaryOptions' => ['id' => 'reg-summary'],
		    'toolbar' => [
				'options' => ['class' => 'pull-left'],
		    	'content' => 
					Html::button('<i class="glyphicon glyphicon-certificate"></i>&nbsp;Award',
						['value' => Url::to(['/awarded-bid/award', 'project_id'  => $model->project_id]),
						'id' => 'awardButton',
						'class' => 'btn btn-default btn-success btn-award',
						'data-title' => 'Award',
					]),
			],
		    'columns' => [
				[ 
					// rowHighlight defaults to true; rowSelectedClass defaults to GridView::TYPE_SUCCESS
   					'class'=>'kartik\grid\RadioColumn',
   					'width'=>'36px',
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
    			[ 
    					'attribute' => 'estimated_hrs', 
    					'label' => 'Hours',
    					'hAlign' => 'right',
        				'format' => ['decimal', 0],
    			],
    			[
    					'attribute' => 'estimate',
    					'visible' => $est_visible,
    					'label' => 'Estimate',
    					'format' => ['decimal', 2],
    					'hAlign' => 'right',
    			], 
    			[
						'attribute' => 'showPdf',
						'label' => 'Doc',
						'format' => 'raw',
						'value' => function($model) {
							return (isset($model->doc_id)) ?
								Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show original agreement']),
										["/registration/show-pdf", 'id'  => $model->id]): '';
							},
            			'contentOptions' => ['style' => 'white-space: nowrap;'],
				],
				[
					'class' => 	'kartik\grid\ActionColumn',
					'controller' => $controller,
					'template' => '{update}{delete}',
					'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
						['value' => Url::to(["/{$controller}/create", 'relation_id'  => $model->project_id]),
						'id' => 'registrationCreateButton',
						'class' => 'btn btn-default btn-modal btn-embedded',
						'data-title' => 'Registration',
					]),
				],
    	],
    ]); ?>
    
    </td><td class="forty-pct datatop">

  <?=  $this->render('../partials/_journal', ['model' => $model, 'noteModel' => $noteModel]) ?>

</td></tr></table>
    
</div>
<?= $this->render('../partials/_modal') ?>