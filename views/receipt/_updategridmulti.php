<?php

use app\models\accounting\AllocatedMemberSearch;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $modelReceipt app\models\accounting\Receipt */
/* @var $membProvider ActiveDataProvider */
/* @var $searchMemb AllocatedMemberSearch */


/** @noinspection PhpUnhandledExceptionInspection */
echo  GridView::widget([
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

        'rowOptions' => function(/** @noinspection PhpUnusedParameterInspection */
            $model) {
            $css = ['verticalAlign' => 'middle'];
            return $css;
        },
        'pjax' => true,
        'pjaxSettings' => ['options' => ['id' => 'member-allocs']],

        'columns' => [
            [
                'class'=>'kartik\grid\ExpandRowColumn',
                'width'=>'50px',
                'value'=>function (/** @noinspection PhpUnusedParameterInspection */
                    $model, $key, $index, $column) {
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
                'urlCreator' => function (/** @noinspection PhpUnusedParameterInspection */
                                            $action, $model, $key, $index) {
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
    ]);

