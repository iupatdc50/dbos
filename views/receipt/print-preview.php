<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\grid\GridView;
// use SebastianBergmann\CodeCoverage\Report\PHP;

/* @var $model app\models\accounting\Receipt */
/* @var $allocProvider ActiveDataProvider */

?>

<div>
<div id="report-title", class="rightside">Receipt</div>

<table class="twentyfive-pct clearfix">
<tr><th class="thiryfive-pct">Received on</th><td><?= Yii::$app->formatter->asDate($model->received_dt, "long") ?></td></tr>
<tr><th>Number</th><td><?= $model->id ?></td></tr>
<tr><th>Total</th><td class="td-bold"><?= Yii::$app->formatter->asCurrency($model->received_amt) ?></td></tr>
</table>

</div>

<table class="hundred-pct"><tr>
<td class="seventyfive-pct">

<?php
	$common_attributes = [
        	[
            		'attribute' => 'payor_nm',
            		'value' => $model->payor_nm . ($model->payor_type == 'O' ? ' (for ' . $model->responsible->employer->contractor . ')' : ''),
    		],
            [
            		'attribute' => 'payment_method',
            		'value' => Html::encode($model->methodText) . ($model->payment_method != '1' ? ' [' . $model->tracking_nbr . ']' : ''),
   			],
        	'unallocated_amt',
        	'remarks:ntext',
        ]; 
?>

<hr>

<?= DetailView::widget([
		'model' => $model,
		'options' => ['class' => 'table table-bordered'],
		'attributes' => array_merge($common_attributes, $model->getCustomAttributes(true)),
]); ?>

</td>

<td></td></table>

<div class="sixty-pct">

<h4>Allocation Summary</h4>

<?= GridView::widget([
		'id' => 'alloc-grid',
		'dataProvider' => $allocProvider,
		'summary' => '',
		'tableOptions' => ['class' => 'table table-bordered'],
		'columns' => [
				'descrip',
				[
						'attribute' => 'amount',
						'format' => ['decimal', 2],
						'contentOptions' => ['class' => 'right'],
						'headerOptions' => ['class' => 'right'],
				],
		],
]); ?>

</div>

<br /><br />

<div class="sign-block">

<h5>Signature  _____________________________________________________________</h5>

</div>

