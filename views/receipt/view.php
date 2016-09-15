<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\receipt */
/* @var $membProvider ActiveDataProvider */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="receipt-view">

    <h1><?= Html::encode('Receipt: ' . $this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?=  Html::button('<i class="glyphicon glyphicon-print"></i>&nbsp;Print', 
							['value' => Url::to(["*"]),
									'id' => 'printButton',
									'class' => 'btn btn-default btn-modal',
									'data-title' => 'Print',
		]); ?>
    </p>
	
	<?= $this->render('../receipt/_detail', ['modelReceipt' => $model]); ?>
	
    <?= GridView::widget([
    		'id' => 'member-grid',
    		'dataProvider' => $membProvider,
        	'filterModel' => $searchMemb,
 			'filterRowOptions'=>['class'=>'filter-row'],
			'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=>'<i class="glyphicon glyphicon-user"></i>&nbsp;Allocated to Members',
		        'before' => false,
		        'after' => false,
		    ],
			'columns' => [
					[
							'class'=>'kartik\grid\ExpandRowColumn',
							'width'=>'50px',
							'value'=>function ($model, $key, $index, $column) {
										return GridView::ROW_COLLAPSED;
									 },
							'detailUrl'=> Yii::$app->urlManager->createUrl(['allocation/summary-json']),
							'headerOptions'=>['class'=>'kartik-sheet-style'],
    						'expandOneOnly'=>true,
				],
				[ 
						'attribute' => 'fullName',
						'format' => 'raw',
						'value' => function ($data) {
							return Html::a ( Html::encode ( $data->member->fullName ), [ 
									'member/view',
									'id' => $data->member_id 
							] );
						} 
				],
        		[
        				'attribute' => 'reportId', 
        				'value' => 'member.report_id',
        				
    			],
				[
						'attribute' => 'totalAllocation',
						'format' => ['decimal', 2],
						'hAlign' => 'right',
    			],
				
			],
	]); ?>
										
	
	
</div>