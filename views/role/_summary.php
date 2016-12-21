<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

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

?>