<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\jui\Accordion;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $statusModel app\models\member\Status */
/* @var $classModel app\models\member\MemberClass */
/* @var $employerModel app\models\member\Employment */

$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-view">

<table class="hundred-pct">
<tr><td class="text-center">
    <?= Html::img($model->imageUrl, ['class' => 'img-thumbnail', 'width'=>'150', 'height'=>'200']) ?>
    <h4><?= Html::encode($this->title) ?></h4>
    <p><?= Html::encode(isset($statusModel) ? $statusModel->lob->short_descrip : 'No Trade')  ?></p>
    <p>
    	<?= Html::button('<i class="glyphicon glyphicon-camera"></i>&nbsp;Update Photo',
						['value' => Url::to(['photo', 'id'  => $model->member_id]),
						'id' => 'photoButton',
						'class' => 'btn btn-default btn-modal btn-embedded',
						'data-title' => 'Photo',
					]) 
		?>
    </p>
    </td><td class="seventyfive-pct">
<p>
        <?= Html::a('Update', ['update', 'id' => $model->member_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->member_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
        'attributes' => [
            'member_id',
            'ssnumber',
            'addressTexts:ntext',
            'phoneTexts:ntext',
            'emailTexts:ntext',
    		'age',
            'birth_dt:date',
            ['attribute' => 'gender', 'value' => Html::encode($model->genderText)],
            'shirt_size',
            ['attribute' => 'local_pac', 'value' => Html::encode($model->localPacText)],
            ['attribute' => 'hq_pac', 'value' => Html::encode($model->hqPacText)],
            'imse_id',
            'specialtyTexts:ntext',
        ],
    ]) ?>
</td></tr>
</table>  

<?php 

    $status = 'Inactive';
    if (isset($statusModel) && is_null($statusModel->end_dt)) {
    	$status = $statusModel->status->descrip;
    }
    $statusUrl = Yii::$app->urlManager->createUrl(['member-status/summary-json', 'id' => $model->member_id]); 
    
    $class = 'Unknown';
    if (isset($classModel)) {
    	$class = $classModel->mClass->descrip . (($classModel->wage_percent < 100) ? ' [' . $classModel->wage_percent . '%]' : '');
    }
    $classUrl = Yii::$app->urlManager->createUrl(['member-class/summary-json', 'id' => $model->member_id]);
    
	$employer = 'Unemployed';
	if (isset($employerModel)) { 
		if (is_null($employerModel->end_dt)) {
			$employer = Html::a($employerModel->contractor->contractor, ['/contractor/view', 'id' => $employerModel->employer]);
			if ($employerModel->is_loaned == 'T') {
				$loanedTo = Html::a($employerModel->duesPayor->contractor, ['/contractor/view', 'id' => $employerModel->dues_payor]);
				$employer .= ' [Loaned to ' . $loanedTo . ']';
			} 
		} else {
			$employer = Html::encode('Unemployed ('. $employerModel->end_dt .')');
		}
			
	} 
	$employerUrl = Yii::$app->urlManager->createUrl(['employment/summary-json', 'id' => $model->member_id]);

?>


<table class="hundred-pct table">
<tr><td class="sixty-pct datatop">
<?=
	Accordion::widget([
		'items' => [
			[
				'header' => Html::tag('span', 'Status: ') . $status ,
				'content' => '<div data-url='.$statusUrl.'>loading...</div>',
			],
			[
				'header' => Html::tag('span', 'Class: ') . $class,
				'content' => '<div data-url='.$classUrl.'>loading...</div>',
			],
			[
				'header' => Html::tag('span', 'Initiation Balance: ') ,
				'content' => 'Feature not yet supported',
			],
			[
				'header' => Html::tag('span', 'Compliance: '),
				'content' => 'Feature not yet supported',
			],
		[
				'header' => Html::tag('span', 'Paid Thru: ')  ,
				'content' => 'Feature not yet supported',
			],
			[
				'header' => Html::tag('span', 'Miscellaneous') ,
				'content' => 'Feature not yet supported',
			],
			[
				'header' => Html::tag('span', 'Employer: ') . $employer,
				'content' => '<div data-url='.$employerUrl.'>loading...</div>',
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
?>
</td><td class="forty-pct datatop">
<div id="journal">
     <?php if ($model->noteCount >= 1): ?>
     	<p> <?= $model->noteCount > 1 ? $model->noteCount . ' Journal Notes' : 'One Journal Note'; ?></p>
     	<?= $this->render('../partials/_notes', ['notes' => $model->notes, 'controller' => 'member-note']); ?>
     <?php endif; ?>

	<?=  $this->render('../partials/_noteform', ['model' => $noteModel]) ?>

</div>
</td></tr></table>

  
</div>
<?= $this->render('../partials/_modal') ?>
