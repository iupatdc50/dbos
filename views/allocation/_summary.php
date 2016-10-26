<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
?>

<table class="hundred-pct"><tr>

<?php if(($duesProvider->getTotalCount() > 0) || ($hrsProvider->getTotalCount() > 0)): ?>

<td class="thirtyfive-pct pad-six"><table>

<?php if($duesProvider->getTotalCount() > 0): ?>

<tr><td>

<?= GridView::widget([
		'id' => 'dues-grid',
		'dataProvider' => $duesProvider,
		'summary' => '',
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

</td></tr>

<?php endif; ?>

<?php if($hrsProvider->getTotalCount() > 0): ?>

<tr><td>

<?= GridView::widget([
		'id' => 'hrs-grid',
		'dataProvider' => $hrsProvider,
		'summary' => '',
		/*
		'panel'=>[
				'type'=>GridView::TYPE_DEFAULT,
				'heading'=>'<i class="glyphicon glyphicon-time"></i>',
		    	'before' => false,
				'after' => false,
				'footer' => false,
		],
		*/
		'columns' => [
				[
						'class' => '\kartik\grid\DataColumn',
						'attribute' => 'allocation_amt',
						'format' => ['decimal', 1],
//						'pageSummary' => true,
						'hAlign' => 'right',
						'label' => 'Hours',
				],
		],
//		'showPageSummary' => true,
]);
?>

</td></tr>

<?php endif; ?>


</table></td><td></td>

<?php endif; ?>

<td class="fortyfive-pct pad-six">

<?php if($allocProvider->getTotalCount() > 0): ?>

<?= GridView::widget([
		'id' => 'alloc-grid',
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

<?php endif; ?>

</tr></tbody></table>