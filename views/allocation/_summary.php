<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
?>

<table class="hundred-pct"><tbody></tbody><tr>

<? if(($duesProvider->getTotalCount() > 0) || ($hrsProvider->getTotalCount() > 0)): ?>

<td class="thirtyfive-pct pad-six">

<? if($duesProvider->getTotalCount() > 0): ?>

<div>

<?= GridView::widget([
		'id' => 'dues_grid',
		'dataProvider' => $duesProvider,
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'Dues',
		    	'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
				[
						'class' => '\kartik\grid\DataColumn',
						'attribute' => 'allocation_amt',
						'format' => ['decimal', 2],
//						'pageSummary' => true,
						'hAlign' => 'right',
		
				],
				[
						'attribute' => 'months',
						'hAlign' => 'right',
				],
				[
						'attribute' => 'paid_thru_dt',
						'format' => 'date',
						'label' => 'Paid Thru',
				],
		],
//		'showPageSummary' => true,
]);
?>

</div>

<? endif; ?>

<? if($hrsProvider->getTotalCount() > 0): ?>

<div class="fifty-pct">

<?= GridView::widget([
		'id' => 'hrs_grid',
		'dataProvider' => $hrsProvider,
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'<i class="glyphicon glyphicon-time"></i>',
		    	'before' => false,
				'after' => false,
				'footer' => false,
		],
		'columns' => [
				[
						'class' => '\kartik\grid\DataColumn',
						'attribute' => 'allocation_amt',
						'format' => ['decimal', 1],
						'pageSummary' => true,
						'hAlign' => 'right',
						'label' => 'Hours',
				],
		],
//		'showPageSummary' => true,
]);
?>


</div>

<? endif; ?>


</td><td></td>

<? endif; ?>

<? if($allocProvider->getTotalCount() > 0): ?>

<td class="fortyfive-pct pad-six">

<?= GridView::widget([
		'id' => 'alloc_grid',
		'dataProvider' => $allocProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=>'Allocations',
		    'before' => false,
			'after' => false,
			'footer' => false,
		],
		'columns' => [
				[
						'attribute' => 'fee_type',
						'value' => 'feeType.extDescrip',
				],
				[
						'class' => '\kartik\grid\DataColumn',
						'attribute' => 'allocation_amt',
						'format' => ['decimal', 2],
						'pageSummary' => true,
						'hAlign' => 'right',
						
				],
				[
						'class' => '\kartik\grid\DataColumn',
						'attribute' => 'assessment_id',
						'value' => function($model, $key, $index, $widget) {
									return (isset($model->assessment_id)) ? $model->assessment->balance : 0.00;
						},
						'label' => 'Balance',
						'format' => ['decimal', 2],
						'hAlign' => 'right',
						
				],
		],
		'showPageSummary' => true,
]);
?>

</td><td></td>

<? endif; ?>

</tr></tbody></table>