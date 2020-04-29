<?php

use app\models\member\Member;
use app\models\member\Status;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $member Member */
/* @var $assessProvider ActiveDataProvider */

?>

	<?php if(Yii::$app->session->hasFlash('balance')): ?>
		<div class="flash-notice"><?= Yii::$app->session->getFlash('balance') ?></div>
	<?php endif; ?>

    <?php if($member->currentStatus->member_status <> Status::INACTIVE): ?>
        <?php if(Yii::$app->user->can('resetPT')): ?>

            <p class="pull-right">
                <?= Html::button('<i class="glyphicon glyphicon-wrench"></i>&nbsp;Repair Paid Thru', [
                    'id' => 'repairHistory',
                    'value' => Url::to(['repair-dues', 'id' => $member->member_id]),
                    'class' => 'btn btn-default btn-modal btn-embedded',
                    'data-title' => 'Last Correct Period',
                    'title' => 'Repair dues paid thru',
                ]) ?>
            </p>

        <?php endif; ?>

        <table class="fifty-pct table table-striped table-bordered detail-view"><tbody>
            <tr>
                <th class="sixty-pct right">Dues Balance</th>
                <td class="right"><?= number_format($member->duesBalance->balance_amt, 2) ?></td>
            </tr>
            <?php foreach ($member->feeBalances AS $balance): ?>
                <tr>
                    <th class="right"><?= $balance->feeType->descrip ?></th>
                    <td class="right"><?= number_format($balance->balance_amt, 2); ?></td>
               </tr>
            <?php endforeach; ?>
            <?php if($member->overage != 0.00): ?>
                <tr>
                    <th class="right">Overage</th>
                    <td class="right negative"><?= $member->overage ?></td>
                </tr>
            <?php endif; ?>
            <tr class="total-border">
                <th class="right">Total Due</th>
                <td class="right<?= $member->allBalance->total_due > 0 ? ' td-danger' : ''; ?>"><?= number_format($member->allBalance->total_due, 2) ?></td>
            </tr>

        </tbody></table>
    <?php endif; ?>

    <div>

        <?= $this->render('../assessment/_summary', [
                'dataProvider' => $assessProvider,
                'relation_id' => $member->member_id,
        ]);  ?>

    </div>


<?= $this->render('../partials/_modal') ?>


