<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

/* @var $dataProvider ActiveDataProvider */

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
				[
						'attribute' => 'child',
						'value' => 'childName.description',
						'label' => 'Included Roles/Permissions',
				],
		],
		
]);

