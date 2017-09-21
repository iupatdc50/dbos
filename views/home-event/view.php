<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $model app\models\HomeEvent */

$dtformat = ['datetime', 'php:n/d/Y h:i A'];

?>
<div class="home-event-view">

    <p>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            [
            		'attribute' => 'start_dt',
            		'format' => ($model->all_day == OptionHelper::TF_TRUE) ? 'date' : $dtformat,
    		],
            [
            		'attribute' => 'end_dt',
            		'format' => ($model->all_day == OptionHelper::TF_TRUE) ? 'date' : $dtformat,
    		],
        	[
        			'attribute' => 'created_by',
        			'value' => $model->createdBy->username,
        	],
        		
        ],
    ]) ?>

</div>
