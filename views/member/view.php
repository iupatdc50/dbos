<?php

use yii\data\ActiveDataProvider;
use yii\widgets\DetailView;
use yii\jui\Accordion;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\accounting\Receipt;
use app\models\member\Status;

/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $emplProvider ActiveDataProvider */
/* @var $noteModel app\models\member\Note */
/* @var $balance string */
/* @var $wage_percent array */

$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$status = $model->currentStatus;
?>
<div class="member-view">

<table class="hundred-pct">
<tr class="datatop">
    <td class="text-center pad-six twentyfive-pct">

        <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title"><?= Html::encode($this->title) ?></h4></div>
            <div class="panel-body">

                <p><?= Html::img($model->imageUrl, ['class' => 'img-thumbnail', 'width'=>'150', 'height'=>'200']) ?></p>
                <p>
                    <?php if(Yii::$app->user->can('updateDemo')): ?>
                    <?= Html::button('<i class="glyphicon glyphicon-camera"></i>&nbsp;Update Photo',
                                    ['value' => Url::to(['photo', 'id'  => $model->member_id]),
                                    'id' => 'photoButton',
                                    'class' => 'btn btn-default btn-modal',
                                    'data-title' => 'Photo',
                                    'disabled' => !(Yii::$app->user->can('updateDemo')),
                                ])
                    ?>
                    <?= Html::a('Clear', ['photo-clear', 'id' => $model->member_id], [
                            'class' => 'btn btn-default',
                            'data' => [
                                    'confirm' => 'Are you sure you want to clear photo?  This cannot be undone.',
                                    'method' => 'post',
                    ]]) ?>
                    <?php endif; ?>
                </p>
                <hr>
                <?= /** @noinspection PhpUnhandledExceptionInspection */
                DetailView::widget([
                    'model' => $model,
                    //			'mode'=>DetailView::MODE_VIEW,
                    'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table-sm text-left'],
                    'attributes' => [
                        [
                            'label' => 'Trade',
                            'value' => Html::encode(isset($status) ? $status->lob->short_descrip : 'No Trade'),
                        ],
                        [
                            'label' => isset($model->recurCcAuth) ? Html::tag('span', 'Recurring', ['class' => 'label label-success pull-left']) . ' Dues Thru' : 'Dues Thru',
                            'attribute' => 'dues_paid_thru_dt',
                            'value' => isset($model->lastDuesReceipt) ?
                                Html::a(date('m/d/Y', strtotime($model->dues_paid_thru_dt)),
                                    '/receipt-' . (($model->lastDuesReceipt->payor_type == Receipt::PAYOR_MEMBER) ? 'member' : 'contractor')
                                    . '/view?id=' . $model->lastDuesReceipt->receipt_id)
                                . Html::button('<i class="glyphicon glyphicon-th-list"></i>', [
                                        'id' => 'duesHistoryButton',
                                        'value' => Url::to(['/member-balances/dues-summary-ajax', 'id' => $model->member_id]),
                                        'class' => 'btn btn-default btn-modal btn-embedded pull-right',
                                        'data-title' => '#Paid Thru History',
                                        'title' => 'Show dues history',
                                        'disabled' => !(Yii::$app->user->can('browseReceipt')),
                                ])
                                : date('m/d/Y', strtotime($model->dues_paid_thru_dt)),
                            'format' => 'raw',
                            'contentOptions' => $model->isPastGracePeriodNotDropped() ? ['class' => 'danger'] : ($model->isDelinquentNotSuspended() ? ['class' => 'warning'] : ['class' => 'default']),
                            'visible' => (Yii::$app->user->can('browseMemberExt') && isset($status) && ($status->member_status != Status::OUTOFSTATE)),
                        ],
                        [
                            'label' => 'Balance Due',
                            'value' =>
                                Html::encode($balance)
                            ,
                            'format' => 'raw',
                            'contentOptions' => ($balance > 0.00) ? ['class' => 'danger'] : ['class' => 'default'],
                            'visible' => Yii::$app->user->can('browseMemberExt') && ($status->member_status != Status::INACTIVE),
                        ],
                    ],
                ]); ?>
                <?php if(isset($model->reinstateStaged)): ?>
                    <div class="flash-notice">Reinstatement preparation complete</div>
                <?php endif; ?>
                <div>
                    <?php if(Yii::$app->user->can('createReceipt') && ($status->member_status != Status::INACTIVE || isset($model->reinstateStaged))) :?>
                    <?=
//                    Html::button('<i class="glyphicon glyphicon-usd"></i> Cash or Check', [
                      Html::button('<i class="glyphicon glyphicon-usd"></i> Create Receipt', [
                        'class' => 'btn btn-default btn-modal',
                        'id' => 'receiptCreateButton',
                        'value' => Url::to(['receipt-member/create', 'lob_cd' => $status->lob_cd, 'id'  => $model->member_id]),
                        'data-title' => 'Receipt',
                        'disabled' => !(isset($status) && isset($model->currentClass)),
                        'title' => 'Create receipt (cash or check)',
                    ]) ?>
                    <?=
//                        Html::button('<i class="glyphicon glyphicon-credit-card"></i> Credit Card', [
                       Html::button('<i class="glyphicon glyphicon-credit-card"></i> Future', [
                        'class' => 'btn btn-default btn-modal',
                        'id' => 'creditCardButton',
                        'value' => Url::to(['receipt-member/credit-card', 'id'  => $model->member_id]),
                        'data-title' => 'Credit Card',
//                        'disabled' => !(isset($status) && isset($model->currentClass)),
                        'disabled' => true,
                        'title' => 'Accept credit card payment',
                    ])
                    ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </td><td class="fiftyfive-pct pad-six">
		<?= $this->render('../partials/_quicksearch', ['className' => 'member']); ?>
        <div><p>
        	<?php if(Yii::$app->user->can('updateDemo')): ?>
				<?= Html::a('Update', ['update', 'id' => $model->member_id], ['class' => 'btn btn-primary']) ?>
				<?php if(Yii::$app->user->can('deleteMember')) :?>
			        <?= Html::a('Delete', ['delete', 'id' => $model->member_id], [
			            'class' => 'btn btn-danger',
			            'data' => [
			                'confirm' => 'Are you sure you want to delete this item?',
			                'method' => 'post',
			            ],
			        ]) ?>
			    <?php endif; ?>
                <?php if(Yii::$app->user->can('browseTraining')): ?>
                    <?=  Html::a(
                        '<i class="glyphicon glyphicon-certificate"></i>&nbsp;Compliance',
                        ['/member-credential/compliance', 'id' => $model->member_id],
                        ['class' => 'btn btn-default'])
                    ?>
                <?php endif; ?>
                <?php if(Yii::$app->user->can('reportAccounting')): ?>
                    <?=  Html::a(
                        '<i class="glyphicon glyphicon-print"></i>&nbsp;Print',
                        ['/member/print-preview', 'id' => $model->member_id],
                        ['class' => 'btn btn-default', 'target' => '_blank'])
                    ?>
                <?php endif; ?>

            <?php endif; ?>
        </p></div>
        <?= /** @noinspection PhpUnhandledExceptionInspection */
        DetailView::widget([
            'model' => $model,
            //		'mode'=>DetailView::MODE_VIEW,
            'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
            'attributes' => [
                'member_id',
                'report_id',
                'imse_id',
                'addressTexts:ntext',
                'phoneTexts:ntext',
                'emailTexts:ntext',
                'age',
                'birth_dt:date',
                ['attribute' => 'gender', 'value' => Html::encode($model->genderText)],
                'shirt_size',
                'pacTexts:ntext',
                //        	'application_dt:date',
                [
                    'attribute' => 'init_dt',
                    'format' => $model->isInApplication() ? NULL : 'date',
                    //        				'rowOptions' => $model->isInApplication() ? ['class' => 'warning'] : ['class' => 'default'],
                    'contentOptions' => $model->isInApplication() ? ['class' => 'warning'] : ['class' => 'default'],
                    'value' => $model->isInApplication() ? '** On Application **' : $model->init_dt,
                ],
                'specialtyTexts:ntext',
                [
                    'format' => 'raw',
                    'value' => isset($model->employer)
                        ? Yii::$app->user->can('browseMemberExt') ? Html::a(Html::encode($model->employer->descrip), '/contractor/view?id=' . $model->employer->dues_payor) : Html::encode($model->employer->descrip)
                        : 'Unemployed',
                    'label' => 'Employer',
                ],
            ],
        ]); ?>
    </td></tr>
</table>  

<?php 

    $status_txt = 'Inactive';
    if(isset($status)) {
        $status_txt = $status->status->descrip;
        if ($model->enrolledOnline)
            $status_txt .= Html::tag('span', " Enrolled for Online Pay", ['class' => 'accord-notice pull-right']);
    }
    $statusUrl = Yii::$app->urlManager->createUrl(['member-status/summary-json', 'id' => $model->member_id]); 

    $class = 'Unknown';
    if (isset($model->currentClass)) {
        $class = $model->currentClass->mClassDescrip;
        if (isset($model->qualifiesForIncrease))
            $class .= Html::tag('span', " Qualifies for step increase to: {$model->qualifiesForIncrease->should_be}%", ['class' => 'accord-alert pull-right']);
    }

    $classUrl = Yii::$app->urlManager->createUrl(['member-class/summary-json', 'id' => $model->member_id]);
    $timesheetUrl = Yii::$app->urlManager->createUrl(['timesheet/summary-json', 'id' => $model->member_id]);

//    $balance = isset($model->currentClass) ? $model->currentClass->mClassDescrip : 'Unknown';
    $balancesUrl = Yii::$app->urlManager->createUrl(['member-balances/summary-json', 'id' => $model->member_id]);

    $historyUrl = Yii::$app->urlManager->createUrl(['receipt-member/summ-flattened-json', 'member_id' => $model->member_id]);

    $employer = isset($model->employer) ? $model->employer->descrip : 'Unemployed';
	$employerUrl = Yii::$app->urlManager->createUrl(['employment/summary-json', 'id' => $model->member_id]);

    $docUrl = Yii::$app->urlManager->createUrl(['member-document/summary-json', 'id' => $model->member_id]);
    $tdocUrl = Yii::$app->urlManager->createUrl(['training-document/summary-json', 'id' => $model->member_id]);

?>


<table class="hundred-pct table">
<tr><td class="datatop">
<?= /** @noinspection PhpUnhandledExceptionInspection */
Accordion::widget([
        'items' => [
            [
                'header' => Html::tag('span', 'Status: ') . $status_txt,
                'content' => '<div data-url=' . $statusUrl . '>loading...</div>',
            ],
            [
                'header' => Html::tag('span', 'Class: ') . $class,
                'content' => '<div data-url=' . $classUrl . '>loading...</div><div class="fifty-pct" data-url=' . $timesheetUrl . '></div>',
            ],
            [
                'header' => Html::tag('span', 'Balances'),
                'content' => '<div class="balances" data-url=' . $balancesUrl . '>loading...</div>',
            ],
            [
                'header' => Html::tag('span', 'Receipt History'),
                'content' => '<div data-url=' . $historyUrl . '>loading...</div>',
            ],
            /*
            [
                'header' => Html::tag('span', 'Employer: ') . $employer,
                'content' => '<div data-url=' . $employerUrl . '>loading...</div>',
            ],
            */
            [
                'header' => Html::tag('span', 'Union Source Documents'),
                'content' => '<div data-url=' . $docUrl . '>loading...</div>',
            ],
            [
                'header' => Html::tag('span', 'Training Source Documents'),
                'content' => '<div data-url=' . $tdocUrl . '>loading...</div>',
            ],
        ],
        'options' => ['tag' => 'div'],
        'headerOptions' => ['tag' => 'div'],
        'itemOptions' => ['tag' => 'div'],
        'clientOptions' => [
            'collapsible' => true,
            'active' => false,
            'heightStyle' => 'content',
        ],
        'clientEvents' => ['activate' => 'fillPanel'],
    ]); ?>
<hr>
        <?php if (Yii::$app->user->can('browseMemberExt')): ?>
            <?= $this->render('../employment/_summary', [
                'dataProvider' => $emplProvider,
                'id' => $model->member_id,
                'employer' => isset($model->employer) ? $model->employer->descrip : 'Unemployed',
                'curr_effective_dt' => isset($model->employer) ? $model->employer->effective_dt : null,
            ]); ?>
        <?php endif; ?>

</td><td class="forty-pct datatop">

<div id="journal">
     <?php if ($model->noteCount >= 1): ?>
     	<p> <?= $model->noteCount > 1 ? $model->noteCount . ' Journal Notes' : 'One Journal Note'; ?></p>
     	<?= $this->render('../partials/_notes', ['notes' => $model->notes, 'controller' => 'member-note']); ?>
     <?php endif; ?>

	<?= /** @noinspection RequireParameterInspection */
    $this->render('../partials/_noteform', ['model' => $noteModel]) ?>

</div>

</td></tr></table>

  
</div>

<?= $this->render('../partials/_modal') ?>

<?php

$script = <<< JS

$('#idcButton').click(function() {
    window.open($(this).attr('value'), 'IDCard', 'width=506,height=750');
});

JS;
// $this->registerJs($script);

?>


