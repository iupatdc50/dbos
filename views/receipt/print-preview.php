<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\grid\GridView;
// use SebastianBergmann\CodeCoverage\Report\PHP;

/* @var $model app\models\accounting\Receipt */
/* @var $allocProvider yii\data\ActiveDataProvider */

?>

<div>
<div id="receipt-title", class="rightside">Receipt</div>

    <h4 class="sm-print">District Council 50 Local <?= $model->lob_cd ?></h4>
    <table class="twentyfive-pct clearfix">
        <tr><th class="thiryfive-pct">Received on</th><td><?= Yii::$app->formatter->asDate($model->received_dt, "long") ?></td></tr>
        <tr><th>Number</th><td><?= $model->id ?></td></tr>
        <tr><th>Total</th><td class="td-bold"><?= Yii::$app->formatter->asCurrency($model->received_amt) ?></td></tr>
    </table>

</div>

<br />

<div>
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

<?php
try {
    echo DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-bordered'],
        'attributes' => array_merge($common_attributes, $model->getCustomAttributes(true)),
    ]);
} catch (Exception $e) {
} ?>

</td>

<td></td></table></div>

<div class="sixty-pct">

<h4 class="sm-print">Allocation Summary</h4>

<?php
try {
    echo GridView::widget([
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
    ]);
} catch (Exception $e) {
} ?>

</div>

<p class="pull-left">&copy; <?= date('Y') ?>
    IUPAT District Council 50</a>. All rights reserved.
</p>



