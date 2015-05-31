<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\project\jtp\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

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
    	'specialColumns' => $specialColumns,
    ]); ?>

</div>
