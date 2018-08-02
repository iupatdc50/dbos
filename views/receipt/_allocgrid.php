<?php

use yii\helpers\Url;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $allocProvider \yii\data\ActiveDataProvider */
/* @var $alloc_memb_id integer */


/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
    'id' => 'itemize-grid',
    'dataProvider' => $allocProvider,
    'pjax' => false,
    'panel'=>[
        'type'=>GridView::TYPE_DEFAULT,
        'heading'=> '<i class="glyphicon glyphicon-tasks"></i>&nbsp;Receipt Allocations',
        'before' => false,
        'after' => false,
        'footer' => false,
    ],
    'columns' => [
        'fee_type',
        [
            'attribute' => 'allocation_amt',
            'class' => 'kartik\grid\EditableColumn',
            'editableOptions' => [
                'formOptions' => ['action' => '/allocation/edit-alloc'],
                'showButtons' => false,
                'buttonsTemplate' => '{submit}',
                'asPopover' => false,
                'pluginEvents' => [
                    'editableSuccess' => "function(event, val, form) { refreshToolBar(); }",
                ],
            ],
            'hAlign' => 'right',
            'vAlign' => 'middle',
            'format' => ['decimal', 2],

        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'title' => Yii::t('app', 'Remove'),
                        'data-confirm' => 'Are you sure you want to delete this item?',
                    ]);
                }
            ],
            'urlCreator' => function ($action, $model, $key, $index) {
                if ($action === 'delete') {
                    $url = '/allocation/delete?id=' . $model->id;
                    return $url;
                }
            },
            'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
                [
                    'value' => Url::to(["/allocation/create", 'alloc_memb_id' => $alloc_memb_id]),
                    'id' => 'allocationCreateButton',
                    'class' => 'btn btn-default btn-modal btn-embedded',
                    'data-title' => 'Allocation',
                ]),

        ],
    ],
//    	'showPageSummary' => true,

]);
?>

<?= $this->render('../partials/_modal') ?>

<?php
$script = <<< JS

$('.kv-editable-link').on('focus', function() {
	$(this).trigger('click');	
});

JS;
$this->registerJs($script);
?>
