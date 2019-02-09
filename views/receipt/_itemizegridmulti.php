<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;


/* @var $searchAlloc app\models\accounting\StagedAllocationSearch */
/* @var $allocProvider yii\data\ActiveDataProvider */
/* @var $modelReceipt app\models\accounting\ReceiptContractor */
/* @var $this \yii\web\View */

$nbsp = '&nbsp;';

$baseColumns = [
        [
                'attribute' => 'classification',
                'value' => 'member.classification.classification',
                'label' => 'Class',
                'width' => '5px',
        ],
        [
            'attribute' => 'reportId',
            'value' => 'member.report_id',
            'contentOptions' => ['style' => 'width:120px'],
        ],
        [
                'attribute' => 'fullName',
                'value' => 'member.fullName',

        ],

];


$feeColumns = [];

foreach ($searchAlloc->fee_types as $fee_type) {
    $feeColumns[] = [
            'attribute' => $fee_type,
            'header' => strtoupper($fee_type),
            'class' => 'kartik\grid\EditableColumn',
            'editableOptions' => [
                    'header' => strtoupper($fee_type),
                    'formOptions' => ['action' => '/staged-allocation/edit-alloc'],
                    'showButtons' => false,
                    'asPopover' => false,
                    'buttonsTemplate' => '{submit}',
                    'pluginEvents' => [
                            'editableSuccess' => "function(event, val, form) { refreshToolBar($modelReceipt->id); }",
                    ],
            ],
            'hAlign' => 'right',
            'vAlign' => 'middle',
            'format' => ['decimal', 2],
    ];
}
$header =
    Html::button('<i class="glyphicon glyphicon-option-horizontal"></i>',
        ['value' => Url::to(["/staged-allocation/add-type", 'receipt_id' => $modelReceipt->id]),
                'id' => 'allocationCreateTypeButton',
                'class' => 'btn btn-default btn-modal',
                'title' => 'Add fee type column',
                'data-title' => 'Fee Type',
    ]) . $nbsp .
    Html::button('<i class="glyphicon glyphicon-plus"></i><i class="glyphicon glyphicon-user"></i>',
        ['value' => Url::to(["/staged-allocation/add", 'receipt_id' => $modelReceipt->id]),
                'id' => 'allocationCreateButton',
                'class' => 'btn btn-default btn-modal',
                'title' => 'Add member allocation line',
                'data-title' => 'Allocation',
    ]);

$actionColumn[] = [
            'class' => 'kartik\grid\ActionColumn',
            'controller' => 'staged-allocation',
            'template' => '{delete}',
            'header' => $header,
            'width' => '110px',
            'buttons' => [
                    'delete' => function ($url, /** @noinspection PhpUnusedParameterInspection */
                                          $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                'title' => Yii::t('app', 'Delete'),
                                'data-confirm' => 'Are you sure you want to delete this allocation item?',
                                'data-method' => 'post',
                                'tabIndex' => '-1',
                        ]);
                    }
            ],


];

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
    'id' => 'itemize-grid',
    'dataProvider' => $allocProvider,
    'filterModel' => $searchAlloc,
    'filterRowOptions' => ['class' => 'filter-row'],
    'pjax' => false,
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
        'heading' => '<i class="glyphicon glyphicon-tasks"></i>&nbsp;Receipt Allocations',
        'before' => '', // prevent 1 display when true
        'after' => false,
    ],
    'toolbar' => [
        'content' =>
            Html::button('Create Member Stub', [
                'class' => 'btn btn-default btn-modal',
                'id' => 'memberCreateButton',
                'value' => Url::to(["/member/create-stub"]),
                'data-title' => 'Member Stub',
            ]),
    ],
    'rowOptions' => function ($model) {
        $member = $model->member;
        $css = ['verticalAlign' => 'middle'];

        if (!isset($member->currentStatus) || ($member->currentStatus->member_status == \app\models\member\Status::STUB))
            $css['class'] = 'text-muted';
        else
            $css['class'] = 'default';
        return $css;
    },


    'columns' => array_merge($baseColumns, $feeColumns, $actionColumn),
    //    	'showPageSummary' => true,

]);

echo $this->render('../partials/_modal');

$script = <<< JS

$('.kv-editable-link').on('focus', function() {
	$(this).trigger('click');	
});

$(document).keydown(function(e) {
    if (e.which === 107 || e.which === 187) {
        $('#allocationCreateButton').click();
    }
})

JS;
$this->registerJs($script);



