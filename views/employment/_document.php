<?php

use app\models\employment\Document9aCard;
use yii\helpers\Html;
use yii\widgets\DetailView;

?>

<div class="document">

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
                        'value' => '<iframe class="thumbnail" src="' . $model->baseDocument->imageUrl . '#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="400" height="200"></iframe>',
                    ],
            ],
        ]);
    ?>

</div>
