<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $model app\models\member\Member */
/* @var $sqlProvider \yii\data\SqlDataProvider */
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

<?php

$baseColumns = [
    [
        'attribute' => 'id',
        'label' => 'Nbr',
    ],
    [
        'attribute' => 'received_dt',
        'format' => ['date', 'php:n/j/Y'],
        'label' => 'Received',
    ],
    'payor',
];

$feeColumns = [];
foreach ($typesSubmitted as $typeSubm) {
    $feeColumns[] = [
        'attribute' => $typeSubm['fee_type'],
        'header' => $typeSubm['fee_type'],
        'class' => 'kartik\grid\DataColumn',
        'hAlign' => 'right',
        'vAlign' => 'middle',
        'format' => ['decimal', 2],
        'pageSummary' => true,
    ];
}

$totalColumn = [[
    'attribute' => 'total',
    'class' => 'kartik\grid\DataColumn',
    'hAlign' => 'right',
    'vAlign' => 'middle',
    'contentOptions' => ['class' => 'grid-rowtotal'],
    'format' => ['decimal', 2],
    'pageSummary' => true,
]];

?>


<div class="page-break"></div>

<h4 class="sm-print">Receipt History</h4>

    <?=
    /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'id' => 'receipt-grid',
        'dataProvider' => $sqlProvider,
//        'resizableColumns' => false,
        'striped' => false,
        'options' => ['class' => 'sm-print'],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'Year: 2018',
            'headingOptions' => ['class'=>'pr-panel-heading'],
            'before' => false,
            'after' => false,
            'footer' => false,

        ],
        'columns' => array_merge($baseColumns, $feeColumns, $totalColumn),
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary default']
    ]);


    ?>



<hr>

<p class="sm-print pull-left">&copy; <?= date('Y') ?>
    IUPAT District Council 50. All rights reserved.
</p>



