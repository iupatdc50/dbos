<?php

// use kartik\detail\DetailView;
use yii\widgets\DetailView;
use yii\jui\Accordion;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\accounting\Receipt;
use app\models\member\Status;

/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $noteModel app\models\member\Note */
/* @var $balance string */

$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-view">

<table class="hundred-pct">
<tr><td class="text-center pad-six">
    <h4><?= Html::encode($this->title) ?></h4>
	<p><?= Html::img($model->imageUrl, ['class' => 'img-thumbnail', 'width'=>'150', 'height'=>'200']) ?></p>
    <p>
       	<?php if(Yii::$app->user->can('updateDemo')): ?>
    	<?= Html::button('<i class="glyphicon glyphicon-camera"></i>&nbsp;Update Photo',
						['value' => Url::to(['photo', 'id'  => $model->member_id]),
						'id' => 'photoButton',
						'class' => 'btn btn-default btn-modal btn-embedded',
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
    <br />
    <?php
    try {
        echo DetailView::widget([
            'model' => $model,
            //			'mode'=>DetailView::MODE_VIEW,
            'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table-sm text-left'],
            'attributes' => [
                [
                    'label' => 'Trade',
                    'value' => Html::encode(isset($model->currentStatus) ? $model->currentStatus->lob->short_descrip : 'No Trade'),
                ],
                [
                    'attribute' => 'dues_paid_thru_dt',
                    'value' => isset($model->lastDuesReceipt) ?
                        Html::a(date('m/d/Y', strtotime($model->dues_paid_thru_dt)),
                            '/receipt-' . (($model->lastDuesReceipt->payor_type == Receipt::PAYOR_MEMBER) ? 'member' : 'contractor')
                            . '/view?id=' . $model->lastDuesReceipt->receipt_id)
                        . Html::button('History', [
                                'id' => 'duesHistoryButton',
                                'value' => Url::to(['/member-balances/dues-summary-ajax', 'id' => $model->member_id]),
                                'class' => 'btn btn-default btn-modal btn-embedded pull-right',
                                'data-title' => 'Paid Thru History',
                                'disabled' => !(Yii::$app->user->can('browseReceipt')),
                        ])
                        : date('m/d/Y', strtotime($model->dues_paid_thru_dt)),
                    'format' => 'raw',
                    'contentOptions' => $model->isPastGracePeriodNotDropped() ? ['class' => 'danger'] : ($model->isDelinquentNotSuspended() ? ['class' => 'warning'] : ['class' => 'default']),
                    'visible' => (Yii::$app->user->can('browseMemberExt') && isset($model->currentStatus) && ($model->currentStatus->member_status != Status::OUTOFSTATE)),
                ],
                [
                    'label' => 'Balance Due',
                    'value' => Html::encode($balance),
                    'contentOptions' => ($balance > 0.00) ? ['class' => 'danger'] : ['class' => 'default'],
                    'visible' => Yii::$app->user->can('browseMemberExt'),
                ],
            ],
        ]);
    } catch (Exception $e) {
    } ?>
    </td><td class="seventyfive-pct">
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
			<?php endif; ?>
        </p></div>
    <?php
    try {
        echo DetailView::widget([
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
//                'drug_test_dt:date',
            ],
        ]);
    } catch (Exception $e) {
    } ?>
</td></tr>
</table>  

<?php 

    $status = isset($model->currentStatus) ? $model->currentStatus->status->descrip : 'Inactive';
    $statusUrl = Yii::$app->urlManager->createUrl(['member-status/summary-json', 'id' => $model->member_id]); 
    
    $class = isset($model->currentClass) ? $model->currentClass->mClassDescrip : 'Unknown';
    $classUrl = Yii::$app->urlManager->createUrl(['member-class/summary-json', 'id' => $model->member_id]);
    
//    $balance = isset($model->currentClass) ? $model->currentClass->mClassDescrip : 'Unknown';
    $balancesUrl = Yii::$app->urlManager->createUrl(['member-balances/summary-json', 'id' => $model->member_id]);

    $complianceUrl = Yii::$app->urlManager->createUrl(['member-credentials/summary-json', 'id' => $model->member_id]);
    
    $employer = isset($model->employer) ? $model->employer->descrip : 'Unemployed';
	$employerUrl = Yii::$app->urlManager->createUrl(['employment/summary-json', 'id' => $model->member_id]);

	$docUrl = Yii::$app->urlManager->createUrl(['member-document/summary-json', 'id' => $model->member_id]);
	
?>


<table class="hundred-pct table">
<tr><td class="sixty-pct datatop">
<?php
try {
    echo Accordion::widget([
        'items' => [
            [
                'header' => Html::tag('span', 'Status: ') . $status,
                'content' => '<div data-url=' . $statusUrl . '>loading...</div>',
            ],
            [
                'header' => Html::tag('span', 'Class: ') . $class,
                'content' => '<div data-url=' . $classUrl . '>loading...</div>',
            ],
            [
                'header' => Html::tag('span', 'Balances'),
                'content' => '<div class="balances" data-url=' . $balancesUrl . '>loading...</div>',
            ],
            [
                'header' => Html::tag('span', 'Compliance'),
//                'content' => '<div data-url=' . $complianceUrl . '>loading...</div>',
                'content' => 'Feature is currently disabled',
            ],
            [
                'header' => Html::tag('span', 'Employer: ') . $employer,
                'content' => '<div data-url=' . $employerUrl . '>loading...</div>',
            ],
            [
                'header' => Html::tag('span', 'Source Documents'),
                'content' => '<div data-url=' . $docUrl . '>loading...</div>',
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
    ]);
} catch (Exception $e) {
}
?>
</td><td class="forty-pct datatop">

<?php if(Yii::$app->user->can('browseMemberExt')):?>

<div id="journal">
     <?php if ($model->noteCount >= 1): ?>
     	<p> <?= $model->noteCount > 1 ? $model->noteCount . ' Journal Notes' : 'One Journal Note'; ?></p>
     	<?= $this->render('../partials/_notes', ['notes' => $model->notes, 'controller' => 'member-note']); ?>
     <?php endif; ?>

	<?=  $this->render('../partials/_noteform', ['model' => $noteModel]) ?>

</div>
<?php endif; ?>

</td></tr></table>

  
</div>

<?= $this->render('../partials/_modal') ?>
