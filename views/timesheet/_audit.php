<?php

use yii\web\View;
use yii\widgets\DetailView;

/* @var $model app\models\training\Timesheet */
/* @var $this View */

?>

<div class="timesheet-audit fifty-pct">

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
        'attributes' => [
            'remarks:ntext',
            [
                'attribute' => 'created_by',
                'value' => $model->enteredBy,
            ],
            [
                'attribute' => 'updated_by',
                'value' => isset($model->updatedBy) && ($model->created_at != $model->updated_at) ? $model->modifiedBy : null,
                'visible' => isset($model->updatedBy) && ($model->created_at != $model->updated_at),
            ]

        ],
    ]); ?>
</div>
