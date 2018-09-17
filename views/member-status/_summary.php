<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\models\member\Status;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$controller = 'member-status';
?>

<div id="statuses">

<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'status-history', 'enablePushState' => false]);

echo GridView::widget([
		'id' => 'status-history',
		'dataProvider' => $dataProvider,
		'pjax' => false,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=>'Status History',
			// workaround to prevent 1 in the before section
			'before' => (Yii::$app->user->can('updateMember')) ? '' : false,
		    'after' => false,
		    // 'footer' => false,
		],
		'toolbar' => [
			'content' =>
				Html::button('<i class="glyphicon glyphicon-refresh"></i>&nbsp;Reset', 
	        		['value' => Url::to(["/member-status/reset", 'member_id'  => $id]), 
            		'id' => 'resetButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Reset',	
            		'title' => 'Reset Dues Paid Thru and/or Initiation',
	        		'disabled' => !(Yii::$app->user->can('resetPT')),
            	])
    			. Html::button('<i class="glyphicon glyphicon-minus-sign"></i>&nbsp;Forfeit', 
            		['value' => Url::to(["/member-status/forfeit", 'member_id'  => $id]), 
            		'id' => 'forfeitButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Forfeit',	
            		'title' => 'Applicant forfeits membership',
            		'disabled' => ($status == Status::INACTIVE),
            	])
				. Html::button('<i class="glyphicon glyphicon-lock"></i>&nbsp;Suspend', 
            		['value' => Url::to(["/member-status/suspend", 'member_id'  => $id]), 
            		'id' => 'suspendButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Suspend',	
            		'disabled' => ($status != Status::ACTIVE),
            	])
    			. Html::button('<i class="glyphicon glyphicon-credit-card"></i>&nbsp;CC', 
            		['value' => Url::to(["/member-status/clear-in", 'member_id'  => $id]), 
            		'id' => 'ccButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Clear In',	
            		'title' => 'Clear In (CCD)',
            		'disabled' => ($status != Status::INACTIVE),
            	])
				. Html::button('Dep ISC', 
            		['value' => Url::to(["/member-status/dep-isc", 'member_id'  => $id]), 
            		'id' => 'depButton',
            		'class' => 'btn btn-default btn-modal',
            		'data-title' => 'Deposit ISC',	
            		'title' => 'Deposit In Service Card',
            		'disabled' => ($status != Status::GRANTINSVC),
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
						'format' => 'date',
						/* Future to make this field updatable inline
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
						'template' => '{update} {delete}',
						'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
								'value' => Url::to(["/{$controller}/create", 'relation_id'  => $id]),
								'id' => 'classCreateButton',
								'class' => 'btn btn-default btn-modal btn-embedded',
								'data-title' => 'Member Status',
						]),
						'visible' => Yii::$app->user->can('updateMember'),
				],
				
		],
]);

?>
</div>
<?php

Pjax::end();

