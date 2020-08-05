<?php

use app\models\employment\Document9aCard;
use yii\helpers\Html;
use yii\widgets\DetailView;

?>

<div class="document seventyfive-pct">

    <?= /** @noinspection PhpUnhandledExceptionInspection */

        /* @var $model Document9aCard */
        DetailView::widget([

            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
            'attributes' => [
                    [
                        'attribute' => 'report ID',
                        'value' => Html::encode($model->member->report_id),
                    ],
                    [
                        'attribute' => 'employee',
                        'value' => Html::encode($model->member->fullName),
                    ],
                    [
                        'attribute' => 'document',
                        'format' => 'raw',
                        'value' => Html::img($model->baseDocument->imageUrl, ['class' => 'img-thumbnail', 'width' => '300', 'height' => '150',]),
                    ],
            ],
        ]);
    ?>

</div>
