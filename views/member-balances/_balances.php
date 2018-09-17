<?php

use app\models\member\Member;

/* @var $this yii\web\View */
/* @var $member Member */
/* @var $dues_balance number */
/* @var $assessment_balance number */
/* @var $assessProvider \yii\data\ActiveDataProvider */

?>

	<?php if(Yii::$app->session->hasFlash('balance')): ?>
		<div class="flash-notice"><?= Yii::$app->session->getFlash('balance') ?></div>
	<?php endif; ?>


	<table class="fifty-pct table table-striped table-bordered detail-view"><tbody>
		<tr>
			<th class="sixty-pct right">Dues Balance</th>
			<td class="right"><?= $dues_balance ?></td>
	   </tr>
		<tr>
			<th class="right">Assessment Balance</th>
			<td class="right"><?= $assessment_balance ?></td>
	   </tr>
	   <?php if($member->overage != 0.00): ?>
		<tr>
			<th class="right">Overage</th>
			<td class="right negative"><?= $member->overage ?></td>
	   </tr>
	   <?php endif; ?>
	   <tr class="total-border">
			<th class="right">Total Due</th>
			<td class="right<?= $dues_balance + $assessment_balance > 0 ? ' td-danger' : ''; ?>"><?= number_format($dues_balance + $assessment_balance - $member->overage, 2) ?></td>
	   </tr>
	   
	</tbody></table>

<div>

<?= $this->render('../assessment/_summary', [
		'dataProvider' => $assessProvider,
		'relation_id' => $member->member_id,
]);  ?>

</div>


<?= $this->render('../partials/_modal') ?>