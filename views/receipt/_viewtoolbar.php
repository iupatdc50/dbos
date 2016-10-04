<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model app\models\accounting\Receipt  */

?>


<p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?=  Html::a('<i class="glyphicon glyphicon-print"></i>&nbsp;Print', ['/receipt-' . $class . '/print-preview', 'id' => $model->id], ['class' => 'btn btn-default']) ?>

    </p>
