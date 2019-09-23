<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;

// Employment

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
/* @var $id string Relational ID around which data is summarized */
/* @var $curr_effective_dt */
?>

<div id="employment-history">

<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'employment-history', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
		'id' => 'employment-history',
		'dataProvider' => $dataProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=>'Employment History',
			// workaround to prevent 1 in the before section
			'before' => (Yii::$app->user->can('updateMember')) ? '' : false,
			'after' => false,
		    // 'footer' => false,
		],
		'toolbar' => [
			'content' => 
				Html::button('<i class="glyphicon glyphicon-saved"></i>&nbsp;Employ',
					['value' => Url::to(["/employment/create", 'relation_id'  => $id]),
					'id' => 'employButton',
					'class' => 'btn btn-default btn-modal',
					'data-title' => 'Employment',
				])
				. Html::button('<i class="glyphicon glyphicon-time"></i>&nbsp;Loan',
					['value' => Url::to(["/employment/loan", 'relation_id'  => $id]),
					'id' => 'loanButton',
					'class' => 'btn btn-default btn-modal',
					'data-title' => 'Lender',
					'disabled' => substr($employer, 0, 10) == 'Unemployed',
				])
				. Html::button('<i class="glyphicon glyphicon-minus-sign"></i>&nbsp;Terminate',
					['value' => Url::to(["/employment/terminate", 'relation_id'  => $id]),
					'id' => 'terminateButton',
					'class' => 'btn btn-default btn-modal',
					'data-title' => 'Terminate',
					'disabled' => substr($employer, 0, 10) == 'Unemployed',
					]),			
		],
		'columns' => [
				'effective_dt:date',
				'end_dt:date',
				[
						'label'	=> 'Employer',
						'format' => 'raw',
						'value' => function($model) {
							return Html::a(Html::encode($model->contractor->contractor), '/contractor/view?id=' . $model->employer);
						},
				],
				[
						'label' => 'Fees Payor',
						'format' => 'raw',
						'value' => function($model) {
							$result = 'Employer';
							if ($model->is_loaned == 'T') {
								if (isset($model->dues_payor)) {
							        $result = Html::a(Html::encode($model->duesPayor->contractor), '/contractor/view?id=' . $model->dues_payor);
								} else {
									$result = '** Check misconfigured loan_to';
								}
							}
							return $result;
						},
				],
				
				[
						'attribute' => 'showPdf',
						'label' => 'Doc',
						'format' => 'raw',
						'value' => function($model) {
							return (isset($model->doc_id)) ?
								Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show referral']),
									$model->imageUrl, ['target' => '_blank', 'data-pjax'=>"0"]) : '';
						},
				],
				
				[
						'class' => 'kartik\grid\ActionColumn',
						'visible' => Yii::$app->user->can('updateMember'),
						// Change the action template because the signatures are not by `id`
						 'template' => '{resume} {edit} {remove}',
						 'buttons' => [
						 		'resume' => function ($url, $model) use ($curr_effective_dt) {
                                    $resume = null;
                                    if (($model->effective_dt == $curr_effective_dt) && (isset($model->end_dt)))
                                        $resume = Html::a('<span class="glyphicon glyphicon-refresh"></span>', $url, [
                                            'title' => Yii::t('app', 'Resume'),
                                            'data-confirm' => 'Are you sure you want to resume employment?',
                                        ]);
                                    return $resume;
					        	},
						 		'edit' => function ($url, $model) use ($curr_effective_dt) {
                                    $edit = null;
                                    if ($model->effective_dt == $curr_effective_dt)
                                        $edit = Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                                            'title' => Yii::t('app', 'Update'),
                                        ]);
                                    return $edit;
						 		}, 
						 		'remove' => function ($url) {
					        		return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
					                	'title' => Yii::t('app', 'Remove'),
					        			'data-confirm' => 'Are you sure you want to delete this item?',
					        		]);
						        }
						  ],
						  'urlCreator' => function ($action, $model) {
						    	if ($action == 'resume') {
                                    $url = '/employment/resume?member_id=' . $model->member_id . '&effective_dt=' . $model->effective_dt;
                                    return $url;
                                } elseif ($action == 'edit') {
						    		$url ='/employment/edit?member_id='.$model->member_id . '&effective_dt='.$model->effective_dt;
						    		return $url;
						    	} elseif ($action === 'remove') {
						        	$url ='/employment/remove?member_id='.$model->member_id . '&effective_dt='.$model->effective_dt;
						        	return $url;
						    	}
						    	return null;
						  }
						  
				],
				
				
		],
]);

?>
</div>
<?php

Pjax::end();

