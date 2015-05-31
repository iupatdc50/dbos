<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

// Member Class

echo GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
				[
						'attribute' => 'mClass',
						'label' => 'Class',
						'value' => 'mClass.descrip',
				],
				'effective_dt:date',
//				'end_dt:date',
				[
						'attribute' => 'rClass',
						'label' => 'Rate',
						'value' => 'rClass.descrip',
				],
				[
						'attribute' => 'wage_percent',
						'label' => 'Percent',
				],
				
		],
]);
