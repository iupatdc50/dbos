<?php

// use kartik\detail\DetailView;
use yii\widgets\DetailView;
use yii\helpers\Url;
use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
$this->title = $model->fullName;

?>
<div class="member-view">

<table class="seventyfive-pct">
<tr><td class="text-center pad-six">
	<h4><?= Html::encode($this->title) ?></h4>
	<p><?= Html::img($model->imageUrl, ['class' => 'img-thumbnail', 'width'=>'150', 'height'=>'200']) ?></p>
	<p>
    	<?= Html::button('<i class="glyphicon glyphicon-camera"></i>&nbsp;Update Photo',
						['value' => Url::to(['photo', 'id'  => $model->member_id]),
						'id' => 'photoButton',
						'class' => 'btn btn-default btn-modal btn-embedded',
						'data-title' => 'Photo',
						'disabled' => !(Yii::$app->user->can('updateMember')),
					]) 
		?>
    </p> 
    </td><td class="seventyfive-pct">
		<?= $this->render('../partials/_quicksearch', ['className' => 'member']); ?>
		<br /><br />
	    <?= DetailView::widget([
	    		'model' => $model,
	           	'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table text-left'],
	        	'attributes' => [
	        		[
	        				'label' => 'Trade', 
	        				'value' => Html::encode(isset($model->currentStatus) ? $model->currentStatus->lob->short_descrip : 'No Trade'),
	    			],
		            'member_id',
		            'report_id',
		        	'addressTexts:ntext',
		            'phoneTexts:ntext',
		            'emailTexts:ntext',
	        	],
	    ]); ?>
	    <?=  $this->render('../member-document/_summary', ['dataProvider' => $docProvider, 'id' => $model->member_id]) ?>
	    
    </td></tr>
</table>  

<table class="hundred-pct table">
<tr><td class="sixty-pct datatop">
</td><td class="forty-pct datatop">
</td></tr></table>

<?= $this->render('../partials/_modal') ?>
