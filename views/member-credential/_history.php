<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $credential_id string */
/* @var $show_attach bool */

?>

<div class="leftside forty-pct">

    <?php
    // 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
    Pjax::begin(['id' => "history{$credential_id}-grid", 'enablePushState' => false]);

    /** @noinspection PhpUnhandledExceptionInspection */
    echo  GridView::widget([
        'id' => "history{$credential_id}-grid",
        'dataProvider' => $dataProvider,
        'pjax' => false,
        'summary' => '',
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'History',
            'before' => false,
            'after' => false,
            'footer' => false,
        ],
        'columns' => [
            'complete_dt:date',
            [
                'attribute' => 'showPdf',
                'visible' => $show_attach,
                'class' => 'kartik\grid\DataColumn',
                'label' => 'Doc',
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function($model) {
                    return (isset($model->doc_id)) ?
                        Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show document']),
                            $model->imageUrl, ['target' => '_blank', 'data-pjax'=>"0"]) : '';
                },

            ],
        ],
    ]);

    Pjax::end();
?>

</div>


