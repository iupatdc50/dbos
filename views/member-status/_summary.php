<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\editable\Editable;
use app\models\member\Status;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$controller = 'member-status';
?>

<?= GridView::widget([
		'id' => 'edit-grid',
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
        			['value' => Url::to(["/member-status/reinstate", 'member_id'  => $id]), 
            		'id' => 'reinstateButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Reinstate',
            		'disabled' => ($status != Status::INACTIVE),
    			])
				. Html::button('<i class="glyphicon glyphicon-warning-sign"></i>&nbsp;Suspend', 
	        		['value' => Url::to(["/member-status/suspend", 'member_id'  => $id]), 
            		'id' => 'suspendButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Suspension',	
            		'disabled' => ($status == Status::INACTIVE) || ($status == Status::SUSPENDED),
            	])
    			. Html::button('<i class="glyphicon glyphicon-hand-down"></i>&nbsp;Drop', 
            		['value' => Url::to(["/member-status/drop", 'member_id'  => $id]), 
            		'id' => 'dropButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Drop',	
            		'disabled' => ($status == Status::INACTIVE),
            	])
    			. Html::button('<i class="glyphicon glyphicon-credit-card"></i>&nbsp;CC', 
            		['value' => Url::to(["/member-status/grant-cc", 'member_id'  => $id]), 
            		'id' => 'dropButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Grant CC',	
            		'disabled' => ($status == Status::INACTIVE),
            	]),
		],
		'columns' => [
				'lob_cd',
				[
						'attribute' => 'status',
						'value' => 'status.descrip',
				],
				[
						'attribute' => 'effective_dt',
						/* Future to make this field updatable inline
						'format' => 'date',
						'class' => 'kartik\grid\EditableColumn',
						'editableOptions' => [
								'inputType' => \kartik\editable\Editable::INPUT_WIDGET,
								'formOptions' => ['action' => '/member-status/edit-item'],
								'showButtons' => false,
								'widgetClass'=> 'kartik\datecontrol\DateControl',
								'options'=>[
										'type'=>\kartik\datecontrol\DateControl::FORMAT_DATE,
										'saveFormat'=>'php:Y-m-d',
										'options'=> [
												'pluginOptions' => [ 
														'autoclose'=>true,
												],
										],
								],
						],
						*/
						
				],
				'end_dt:date',
				[
						'attribute' => 'reason',
						/* Future to make this field updatable inline
						'class' => 'kartik\grid\EditableColumn',
						'editableOptions' => [
								'inputType' => \kartik\editable\Editable::INPUT_TEXTAREA,
								'formOptions' => ['action' => '/member-status/edit-item'],
								'showButtons' => false,
						],
						*/
						
				],
				[
						'class' => \yii\grid\ActionColumn::className(),
						'controller' => $controller,
						'template' => '{delete}',
						'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
								'value' => Url::to(["/{$controller}/create", 'relation_id'  => $id]),
								'id' => 'classCreateButton',
								'class' => 'btn btn-default btn-modal btn-embedded',
								'data-title' => 'Member Status',
						]),
				],
				
		],
]);
