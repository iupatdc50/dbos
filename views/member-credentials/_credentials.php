<?php

/* @var $this yii\web\View */
/* @var $member app\models\member\Member */
/* @var $recurProvider yii\data\ActiveDataProvider */
/* @var $nonrecurProvider yii\data\ActiveDataProvider */
/* @var $medtestsProvider yii\data\ActiveDataProvider */
/* @var $coreProvider yii\data\ActiveDataProvider */

?>

<table class="table-bordered">
    <tbody>
    <tr>
        <td class="datatop sixty-pct">
            <?= $this->render('_summary', [
                    'dataProvider' => $recurProvider,
                    'relation_id' => $member->member_id,
                    'heading' => 'Recurring',
                    'expires' => true,
            ]); ?>
        </td>
        <td class="datatop forty-pct">
            <?= $this->render('_summary', [
                'dataProvider' => $nonrecurProvider,
                'relation_id' => $member->member_id,
                'heading' => 'Non-Recurring',
                'expires' => false,
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="datatop">
            <?= $this->render('_summary', [
                'dataProvider' => $medtestsProvider,
                'relation_id' => $member->member_id,
                'heading' => 'Medical Tests',
                'expires' => true,
            ]); ?>
        </td>
        <td class="datatop">
            <?= $this->render('_summary', [
                'dataProvider' => $coreProvider,
                'relation_id' => $member->member_id,
                'heading' => 'PD Core',
                'expires' => false,
            ]); ?>

        </td>
    </tr>
    </tbody>
</table>

