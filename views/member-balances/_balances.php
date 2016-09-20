<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dues_balance string */

?>

    <p class="pull-right">
    	<?= Html::button('Create Receipt', [
							'class' => 'btn btn-default btn-modal',
							'id' => 'receiptCreateButton',
							'value' => Url::to(['create-member/create', 'id'  => $member->member_id]),
							'data-title' => 'Receipt',
		]) . ' ' .
		
    	Html::button('<i class="glyphicon glyphicon-th-list"></i>&nbsp;Show Receipts',[
    					'value' => Url::to(['/receipt-member/summary-ajax', 'id'  => $member->member_id]),
						'id' => 'receiptsButton',
						'class' => 'btn btn-default btn-modal',
						'data-title' => 'receipts',
		]) ?>
    </p> 

<? 
$total_due = number_format($dues_balance + $assessment_balance, 2);
$bkground = $total_due > 0 ? ' td-danger' : ''; ?>

	<table class="fifty-pct table table-striped table-bordered detail-view"><tbody>
		<tr>
			<th class="sixty-pct right">Dues Balance</th>
			<td class="right"><?= $dues_balance ?></td>
	   </tr>
		<tr>
			<th class="right">Assessment Balance</th>
			<td class="right"><?= $assessment_balance ?></td>
	   </tr>
		<tr class="total-border">
			<th class="right">Total Due</th>
			<td class="right<?= $bkground ?>"><?= $total_due ?></td>
	   </tr>
	   
	</tbody></table>

<div>

<?= $this->render('../assessment/_summary', [
		'dataProvider' => $assessProvider,
		'relation_id' => $member->member_id,
]);  ?>

</div>


<?= $this->render('../partials/_modal') ?>