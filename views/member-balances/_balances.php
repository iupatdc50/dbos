<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

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
<? 

$controller = 'assessment';

echo GridView::widget([
		'dataProvider' => $assessProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=>'Open Assessments',
			'before' => false,
		    'after' => false,
		    'footer' => false,
		],
		'columns' => [
				'created_at:date',
				'fee_type',
				'assessment_amt',
				'purpose',
				[
						'class' => \yii\grid\ActionColumn::className(),
						'controller' => $controller,
						'template' => '{delete}',
						'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
								'value' => Url::to(["/{$controller}/create", 'relation_id'  => '*']),
								'id' => 'assessmentCreateButton',
								'class' => 'btn btn-default btn-modal btn-embedded',
								'data-title' => 'Assessment',
						]),
				],
		],
]);

?>

</div>


<?= $this->render('../partials/_modal') ?>