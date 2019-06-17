<?php

/**
 * Because this view uses an SqlDataProvider, which returns an array, accessing content uses $model[col] notation.
 */

use app\models\member\Member;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $member Member */
/* @var $image_path string */

$this->title = 'Timesheets for ' . $member->fullName;

$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['/member/index']];
$this->params['breadcrumbs'][] = ['label' => $member->fullName, 'url' => ['/member/view', 'id' => $member->member_id]];
$this->params['breadcrumbs'][] = 'Timesheets';

?>
<div class="timesheet-index">

    <?php

        $baseColumns = [
//            'id',
//            'acctMonthText',
            [
                'attribute' => 'acct_month',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
        ];

        $hourColumns = [];
        foreach ($member->processes as $process)
        {
            $hourColumns[] = [
                'attribute' => $process['descrip'],
                'class' => 'kartik\grid\DataColumn',
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 2],
                'headerOptions' => ['class' => 'vertical-colhead'],
            ];
        }

        $aftColumns = [
            [
                'attribute' => 'total',
                'class' => 'kartik\grid\DataColumn',
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 2],
                'label' => 'TOTAL',
                'headerOptions' => ['class' => 'vertical-colhead'],
                'contentOptions' => ['class' => 'grid-rowtotal'],
            ],
            [
                'attribute' => 'showPdf',
                'label' => 'Doc',
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function($model) {
                    return (isset($model['doc_id'])) ?
                        Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show DPR']),
                            $model['imageUrl'], ['target' => '_blank', 'data-pjax'=>"0"]) : '';
                },
            ],
            'remarks',
            [
                'attribute' => 'username',
                'label' => 'Entered by',
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:n/j/Y'],
                'label' => 'Entry Date',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                'template' => '{attach} {update} {delete}',
                'buttons' => [
                    'attach' => function ($url, $model) {
                        return Html::button('<i class="glyphicon glyphicon-save-file"></i>',
                            [
                                'value' => $url,
                                'id' => 'attachButton' . $model['id'],
                                'class' => 'btn btn-default btn-modal btn-detail btn-link',
                                'title' => Yii::t('app','Attach document'),
                                'data-title' => 'Attach',
                            ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::button('<i class="glyphicon glyphicon-pencil"></i>',
                            [
                                'value' => $url,
                                'id' => 'updateButton' . $model['id'],
                                'class' => 'btn btn-default btn-modal btn-detail btn-link',
                                'title' => Yii::t('app','Update'),
                                'data-title' => 'Update',
                            ]);
                    },
                    'delete' => function ($url) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => Yii::t('app', 'Delete'),
                            'data-confirm' => 'Are you sure you want to delete this timesheet?',
                            'data-method' => 'post',
                        ]);
                    }
                ],
                'urlCreator' => function ($action, $model) {
                    if ($action === 'attach') {
                        $url = '/timesheet/attach?id=' . $model['id'];
                        return $url;
                    } elseif ($action === 'update') {
                        $url = '/timesheet/update?id=' . $model['id'];
                        return $url;
                    } elseif ($action === 'delete') {
                        $url = '/timesheet/delete?id=' . $model['id'];
                        return $url;
                    }
                    return null;
                },
                'visible' => Yii::$app->user->can('manageTraining'),
            ],
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'panel'=>[
                'type'=>GridView::TYPE_PRIMARY,
                'heading'=> $this->title,
                // workaround to prevent 1 in the before section
                'before' => (Yii::$app->user->can('manageTraining')) ? '' : false,
                'after' => false,
            ],
            'toolbar' => [
                'content' =>
                    Html::button('Create Timesheet',
                        [
                            'class' => 'btn btn-success btn-modal',
                            'id' => 'timesheetCreateButton',
                            'value' => Url::to(["/timesheet/create", 'member_id' => $member->member_id]),
                            'data-title' => 'Timesheet',
                        ]),
            ],

            'rowOptions' => function($model) {
                $css = [];
                $css['class'] = ($model['computed'] == $model['total']) ? 'default' : 'danger';
                return $css;
            },

            'formatter' => [
                'class' => 'yii\i18n\Formatter',
                'nullDisplay' => '',
            ],
            'columns' => array_merge($baseColumns, $hourColumns, $aftColumns),
        ]);
    ?>
</div>

<?= $this->render('../partials/_modal') ?>


