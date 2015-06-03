<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
?>

<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=>'Status History',
		    'after' => false,
		    'footer' => false,
		],
		'toolbar' => [
			'content' => 
				Html::button('<i class="glyphicon glyphicon-repeat"></i>&nbsp;Reinstate', 
        			['value' => Url::to(["/accounting/reinstate", 'member_id'  => $id]), 
            		'id' => 'suspendButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Reinstate',
            		'disabled' => true,
//            		'disabled' => ($status == 'Active'),
    			])
				. Html::button('<i class="glyphicon glyphicon-warning-sign"></i>&nbsp;Suspend', 
	        		['value' => Url::to(["/status/suspend", 'member_id'  => $id]), 
            		'id' => 'suspendButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Suspension',	
            		'disabled' => true,
//            		'disabled' => ($status == 'Inactive') || ($status == 'Suspend'),
            	])
    			. Html::button('<i class="glyphicon glyphicon-hand-down"></i>&nbsp;Drop', 
            		['value' => Url::to(["/status/drop", 'member_id'  => $id]), 
            		'id' => 'dropButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Drop',	
            		'disabled' => true,
//            		'disabled' => ($status == 'Inactive'),
            	]),
		],
		'columns' => [
				'lob_cd',
				[
						'attribute' => 'status',
						'value' => 'status.descrip',
				],
				'effective_dt:date',
				'end_dt:date',
				'reason',
		],
]);
