<?php

use app\models\accounting\AllocatedMemberSearch;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $modelReceipt app\models\accounting\Receipt */
/* @var $membProvider ActiveDataProvider */
/* @var $searchMemb AllocatedMemberSearch */

$this->title = 'Update Receipt: ' . ' ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $modelReceipt->id, 'url' => ['view', 'id' => $modelReceipt->id]];
$this->params['breadcrumbs'][] = 'Update';

?>
<div class="receipt-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $form = ActiveForm::begin([
        'enableClientValidation' => true,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $this->render('../receipt/_updatetoolbar', [
        'modelReceipt' => $modelReceipt,
    ]) ?>

    <div class="leftside forty-pct">


    <?= $form->field($modelReceipt, 'payor_nm', [
        'addon' => [
            'append' => [
                'content' => Html::button('<i class="glyphicon glyphicon-transfer"></i>&nbsp;Change Employer', [
                    'value' => Url::to(['/responsible-employer/update', 'id' => $modelReceipt->id]),
                    'class' => 'btn btn-default btn-modal',
                    'data-title' => 'Reassign',
                    'title' => Yii::t('app', 'Change Employer'),
                ]),
                'asButton' => true
            ]
        ]
    ]) ?>

    <?= $this->render('../receipt/_formfields', [
        'form' => $form,
        'model' => $modelReceipt,
    ]) ?>

    <?= $form->field($modelReceipt, 'unallocated_amt')->textInput(['maxlength' => true, 'readonly' => (!$modelReceipt->isNewRecord)]) ?>

    <?= $this->render('../receipt/_helperfields', [
        'form' => $form,
        'model' => $modelReceipt,
    ]) ?>

    <?php ActiveForm::end(); ?>

    </div>

    <div class="rightside fiftyfive-pct">

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'id' => 'member-grid',
        'dataProvider' => $membProvider,
        'filterModel' => $searchMemb,
        'filterRowOptions'=>['class'=>'filter-row'],
        'panel'=>[
            'type'=>GridView::TYPE_DEFAULT,
            'heading'=>'<i class="glyphicon glyphicon-user"></i>&nbsp;Allocations',
            'before' => false,
            'after' => false,
        ],

        'rowOptions' => function($model) {
            $css = ['verticalAlign' => 'middle'];
            return $css;
        },
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'member-allocs']],

        'columns' => [
            [
                'class'=>'kartik\grid\ExpandRowColumn',
                'width'=>'50px',
                'value'=>function ($model, $key, $index, $column) {
                    return GridView::ROW_COLLAPSED;
                },
                'detailUrl'=> Yii::$app->urlManager->createUrl(['allocation/update-grid-ajax']),
                'headerOptions'=>['class'=>'kartik-sheet-style'],
                'expandOneOnly'=>true,
//                'onDetailLoaded' => "function() { $.pjax.reload({container:'#member-allocs'}); }",
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'classification',
                'value' => 'member.classification.classification',
                'label' => 'Class',
                'width' => '5px',
            ],
            [
                'attribute' => 'fullName',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a ( Html::encode ( $data->member->fullName ), [
                        'member/view',
                        'id' => $data->member_id
                    ] );
                }
            ],
            [
                'attribute' => 'reportId',
                'value' => 'member.report_id',

            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'totalAllocation',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{reassign} {delete}',
                'noWrap' => true,
                'buttons' => [
                    'reassign' => function ($url, $model) {
                        return Html::button('<i class="glyphicon glyphicon-transfer"></i>',
                            [
                                'value' => $url,
                                'id' => 'reassignButton' . $model->id,
                                'class' => 'btn btn-default btn-modal btn-detail',
                                'title' => Yii::t('app','Reassign to another member'),
                                'data-title' => 'Reassign',
                            ]);
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    return "/allocated-member/{$action}?id={$model->id}";
                },
                'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', [
                    'value' => Url::to(["/allocated-member/create", 'receipt_id' => $modelReceipt->id]),
                    'id' => 'allocmembCreateButton',
                    'class' => 'btn btn-default btn-modal btn-embedded',
                    'data-title' => 'Create',
                ]),
            ],

        ],
    ]); ?>
    </div>

</div>

<?= $this->render('../partials/_modal') ?>

