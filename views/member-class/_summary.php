<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $id string */

// Member Class
$controller = 'member-class';
?>

<div class="form-group">

<?= GridView::widget([
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
				
							[
									'class' => \yii\grid\ActionColumn::className(),
									'controller' => $controller,
									'template' => '{delete}',
									'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
											['value' => Url::to(["/{$controller}/create", 'relation_id'  => $id]),
													'id' => 'classCreateButton',
													'class' => 'btn btn-default btn-modal btn-embedded',
													'data-title' => 'Member Class',
											]),
							],
		],
]); ?>
				
</div>				
