<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\grid\GridView;

?>

<div class="assessment-view">

<table class="hundred-pct"><tr><td class="thirtyfive-pct">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
        		'assessment_dt:date',
        		[
        				'attribute' => 'fee_type',
        				'value' => Html::encode($model->feeType->extDescrip),
    			],
        		[
        				'attribute' => 'assessment_amt',
        				'format' => ['decimal', 2],
        				'hAlign' => 'right',
        		],
        		'purpose:ntext',
        ],
    ]) ?>

</td><td></td><td class="fifty-pct">

<?= GridView::widget([
		'id' => 'allocation-grid',
		'dataProvider' => $allocProvider,
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'Allocated',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
				'allocatedMember.receipt_id',
				'allocatedMember.receipt.received_dt:date',
				'allocation_amt',
    			[
		    			'class' => 'yii\grid\ActionColumn',
    					'contentOptions' => ['style' => 'white-space: nowrap;'],
    					'template' => '{view}',
    					'buttons' => [
    						'view' => function($url, $model, $key) {
    			    					return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['title' => 'View']);
    			    				},
    			    	],
    			    	'urlCreator' => function ($action, $model, $key, $index) {
    			    						if ($action === 'view') {
    			    							$route = ($model->allocatedMember->receipt->payor_type == 'C') ? '/receipt-contractor' : '/receipt-member';
    			    							$url = Yii::$app->urlManager->createUrl([$route . '/view', 'id' => $model->allocatedMember->receipt_id]);
    			    							return $url;
    			    						}
    			    	},
    			    	'contentOptions' => ['style' => 'white-space: nowrap;'],
            	],
		],
]);

?>
</td></tr></table>
        		
        		
</div>

