<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

// Employment

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $id string Relational ID around which data is summarized */
?>

<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=>'Employment History',
		    'after' => false,
		    'footer' => false,
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
				])
				. Html::button('<i class="glyphicon glyphicon-minus-sign"></i>&nbsp;Terminate',
					['value' => Url::to(["/employment/terminate", 'relation_id'  => $id]),
					'id' => 'terminateButton',
					'class' => 'btn btn-default btn-modal',
					'data-title' => 'Terminate',
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
						'attribute' => 'duesPayor',
						'format' => 'raw',
						'value' => function($model) {
							$result = 'Member';
							if (isset($model->dues_payor)) {
								if ($model->is_loaned == 'T') {
							        $result = Html::a(Html::encode($model->duesPayor->contractor), '/contractor/view?id=' . $model->dues_payor);
								} else {
									$result = 'Employer';
								}
							}
							return $result;
						},
				],
				
		],
]);


