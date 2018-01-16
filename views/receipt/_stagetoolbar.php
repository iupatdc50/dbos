<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $modelReceipt app\models\accounting\Receipt  */

?>


<div id="running-display" class="pull-right">
    <div id="running-total" class="flash-success"></div>
</div>
<div id="balance-display" class="pull-right">
    <div id="out-of-balance" class="flash-error"></div>
</div>

<div><p>
        <?= Html::a('Post', ['post', 'id' => $modelReceipt->id], ['id' => 'post-btn', 'class' => 'btn btn-primary']) ?>
        <?= Html::button('<i class="glyphicon glyphicon-check"></i>&nbsp;Balance', [
					'value' => Url::to(["balance", 'id' => $modelReceipt->id]),
					'id' => 'balance-btn',
					'class' => 'btn btn-default btn-modal',
					'data-title' => 'Adjustments',
		]); ?>
        <?= Html::a('Cancel', ['cancel', 'id' => $modelReceipt->id], [
       				'class' => 'btn btn-danger',
	            	'data' => [
	                		'confirm' => "Are you sure you want to proceed? You will lose the data you entered, and receipt number: `{$modelReceipt->id}` cannot be reused.",
	                		'method' => 'post',
	            	],
	    ]) ?>
</p></div>

<?php
$script = <<< JS

$(function() {
		refreshToolBar($modelReceipt->id);
})
		
function refreshToolBar(id) {
	$.get('$controller/balances-json', { id : id }, function(data) {
		// noinspection JSUndeclaredVariable
        result = $.parseJSON(data);
        if (result.balance == 0.00) {
			$('#balance-display').hide();
			$('#balance-btn').hide();
			$('#post-btn').show();
		} else {
			$('#out-of-balance').html("Out of Balance: " + result.balance);
			$('#balance-display').show();
			$('#balance-btn').show();
			$('#post-btn').hide();
		}
		$('#running-total').html("Total Allocation: " + result.running);
	});
}

JS;

$pos = \yii\web\View::POS_READY;
$this->registerJs($script, $pos);
?>
