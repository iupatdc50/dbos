<?php

use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $model app\models\accounting\Receipt */
/* @var $allocProvider yii\data\ActiveDataProvider */
/* @var $common_attributes array */

/** @noinspection PhpUnhandledExceptionInspection */
$received_dt = Yii::$app->formatter->asDate($model->received_dt, "long");
/** @noinspection PhpUnhandledExceptionInspection */
$received_amt = Yii::$app->formatter->asCurrency($model->received_amt);

?>

<div id="receipt-title" class="rightside">Receipt</div>

<h4 class="sm-print">District Council 50 Local <?= $model->lob_cd ?></h4>
<table class="sm-table twentyfive-pct clearfix">
    <tr><th class="thiryfive-pct">Received on</th><td><?= $received_dt ?></td></tr>
    <tr><th>Number</th><td><?= $model->id ?></td></tr>
    <tr><th>Total</th><td class="td-bold"><?= $received_amt ?></td></tr>
</table>

<br />

<table class="hundred-pct"><tr>
        <td>

            <?=
            /** @noinspection PhpUnhandledExceptionInspection */
            DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'sm-table table-bordered'],
                'attributes' => array_merge($common_attributes, $model->getCustomAttributes(true)),
            ]);
            ?>
        </td>

        <td>

            <h4 class="sm-print">Allocation Summary</h4>

            <?=
            /** @noinspection PhpUnhandledExceptionInspection */
            GridView::widget([
                'id' => 'alloc-grid',
                'dataProvider' => $allocProvider,
                'summary' => '',
                'tableOptions' => ['class' => 'sm-table table-bordered'],
                'columns' => [
                    'descrip',
                    [
                        'attribute' => 'amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'right'],
                        'headerOptions' => ['class' => 'right'],
                    ],
                ],
            ]);
            ?>

        </td>

        </td>
</tr></table>

<hr>

<p class="sm-print pull-left">&copy; <?= date('Y') ?>
    IUPAT District Council 50. All rights reserved.
</p>
