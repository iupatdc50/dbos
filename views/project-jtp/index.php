<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\project\jtp\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $total_hold number */

$this->title = 'JTP Projects';
$this->params['breadcrumbs'][] = $this->title;

$specialColumns = [
	[
		'attribute' => 'hold',
		'label' => 'Hold Amount',
		'value' => 'holdAmount.hold_amt',
		'hAlign' => 'right',
		'format' => ['decimal', 2],
	],	
];

?>

<div class="project-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= $this->render('../project/_maingrid', [
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
    	'heading' => $this->title,
    	'before' => 'Total Hold Amount: $' . number_format($total_hold, 2),
    	'specialColumns' => $specialColumns,
    ]); ?>

</div>
