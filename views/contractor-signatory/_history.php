<?php

use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
/* @var $id string   License number */

?>

<div class="leftside forty-pct">

    <?=
        /** @noinspection PhpUnhandledExceptionInspection */
        GridView::widget([
            'id' => 'signatory-history',
            'dataProvider' => $dataProvider,
            'panel'=>[
                    'type'=>GridView::TYPE_DEFAULT,
                    'heading'=>'<i class="glyphicon glyphicon-edit"></i>&nbsp;History',
                    'class' => 'text-primary',
                    'before' => false,
                    'after' => false,
                    'footer' => false,
            ],
            'columns' => [
                    'signed_dt:date',
                    [
                            'attribute' => 'showPdf',
                            'label' => 'Doc',
                            'format' => 'raw',
                            'value' => function($model) {
                                return (isset($model->doc_id)) ?
                                    Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show original agreement']),
                                        $model->imageUrl, ['target' => '_blank']) : '';
                            },
                    ],
            ],
        ]); ?>

</div>
