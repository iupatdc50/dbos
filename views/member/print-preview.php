<?php

use yii\data\SqlDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $model app\models\member\Member */
/* @var $sqlProvider SqlDataProvider */
/* @var $receipts array */
/* @var $totals array */
/* @var $typesSubmitted array fee types submittted */

?>

    <h4 class="sm-print"><?= $model->fullName ?></h4>
    <p><?= Html::img($model->imageUrl, ['class' => 'img-thumbnail', 'width' => '75', 'height' => '100',
        ]) ?></p>
    <p>Member Profile Report</p>


<br />

<?=
/** @noinspection PhpUnhandledExceptionInspection */
DetailView::widget([
    'model' => $model,
    'options' => ['class' => 'sm-table table-bordered seventyfive-pct'],
    'attributes' => [
        [
            'label' => 'Trade',
            'value' => Html::encode(isset($model->currentStatus) ? $model->currentStatus->lob->short_descrip : 'No Trade'),
        ],
        'member_id',
        'report_id',
        'imse_id',
        [
            'label' => 'Status',
            'value' => isset($model->currentStatus) ?
                $model->currentStatus->status->descrip . ' (' . date('m/d/Y', strtotime($model->currentStatus->effective_dt)) . ')':
                'Inactive',
        ],
        [
            'label' => 'Classification',
            'value' => isset($model->currentClass) ?
                $model->currentClass->mClassDescrip . ' (' . date('m/d/Y', strtotime($model->currentClass->effective_dt)) . ')' :
                'Unknown',
        ],
        'addressTexts:ntext',
        'phoneTexts:ntext',
        'emailTexts:ntext',
        'birth_dt:date',
        ['attribute' => 'gender', 'value' => Html::encode($model->genderText)],
        'shirt_size',
        'pacTexts:ntext',
        'application_dt:date',
        [
            'attribute' => 'init_dt',
            'format' => $model->isInApplication() ? NULL : 'date',
            'value' => $model->isInApplication() ? '** On Application **' : $model->init_dt,
        ],
        'specialtyTexts:ntext',
        'dues_paid_thru_dt:date',
        [
            'label' => 'Employer',
            'value' => isset($model->employer) ? $model->employer->descrip : 'Unemployed',
        ],

    ],
]);
?>

    <table class="sm-table hundred-pct"><tr>
            <td class="seventyfive-pct">
    <h4 class="sm-print">Journal Notes</h4>
    <table class="table table-bordered"><th>Author</th><th>Time</th><th>Note</th>

        <?php foreach ($model->notes as $entry): ?>
            <tr>
                <td>
                    <?= $entry->author->username ?>
                </td><td>
                    <?= date('m/d/y h:i a', $entry->created_at) ?>
                </td><td>
                    <?= $entry->note ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</td></tr></table>

<p class="page-break"></p>

<h4 class="sm-print">Receipt History</h4>

<table class="sm-table table-bordered hundred-pct">

    <thead>
        <tr>
            <th>Nbr</th>
            <th>Received</th>
            <th>Payor</th>
            <?php foreach ($typesSubmitted as $typeSubm): ?>
                <th class="right"><?= $typeSubm['fee_type']; ?></th>
            <?php endforeach; ?>
            <th class="right">Total</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach($receipts as $receipt): ?>
        <tr>
            <td><?= $receipt['id'] ?></td>
            <td><?= date('m/d/Y', strtotime($receipt['received_dt'])) ?></td>
            <td><?= $receipt['payor'] ?></td>

            <?php foreach ($typesSubmitted as $typeSubm): ?>
                <td class="right"><?= $receipt[$typeSubm['fee_type']]; ?></td>
            <?php endforeach; ?>

            <th class="right"><?= $receipt['total'] ?></th>
        </tr>
    <?php endforeach; ?>
    </tbody>

    <tfoot>
        <tr>
            <td></td><td></td><th class="right">Totals</th>

            <?php foreach ($typesSubmitted as $typeSubm): ?>
                <th class="right"><?= $totals[$typeSubm['fee_type']]; ?></th>
            <?php endforeach; ?>

            <th class="right"><?= $totals['total'] ?></th>
        </tr>
    </tfoot>

</table>


<hr>

<p class="sm-print pull-left">&copy; <?= date('Y') ?>
    IUPAT District Council 50. All rights reserved.
</p>



