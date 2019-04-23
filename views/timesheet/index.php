<?php

use app\models\member\Member;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $member Member */
/* @var $processes array */

$this->title = 'Timesheets for ' . $member->fullName;
$this->params['breadcrumbs'][] = $this->title;
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
        foreach ($processes as $process)
        {
            $hourColumns[] = [
                'attribute' => $process['work_process'],
                'class' => 'kartik\grid\DataColumn',
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 1],
                'headerOptions' => ['class' => 'vertical-colhead'],
            ];
        }

        $aftColumns = [
            [
                'attribute' => 'total',
                'class' => 'kartik\grid\DataColumn',
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 1],
                'label' => 'TOTAL',
                'headerOptions' => ['class' => 'vertical-colhead'],
                'contentOptions' => ['class' => 'grid-rowtotal'],
            ],
            [
                'attribute' => 'doc_id',
                'label' => 'Doc',
            ],
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
                'template' => '{update} {delete}',
            ],
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'panel'=>[
                'type'=>GridView::TYPE_PRIMARY,
                'heading'=> $this->title,
                // workaround to prevent 1 in the before section
                'before' => (Yii::$app->user->can('createReceipt')) ? '' : false,
                'after' => false,
            ],
            'toolbar' => [
                'content' =>
                    Html::button('Create Timesheet',
                        [
                            'class' => 'btn btn-success btn-modal',
                            'id' => 'timesheetCreateButton',
                            'value' => Url::to(["*"]),
                            'data-title' => 'Timesheet',
                            'disabled' => true,
                        ]),
            ],
            'formatter' => [
                'class' => 'yii\i18n\Formatter',
                'nullDisplay' => '',
            ],
            'columns' => array_merge($baseColumns, $hourColumns, $aftColumns),
        ]);
    ?>
</div>

<?= $this->render('../partials/_modal') ?>


