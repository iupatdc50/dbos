<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\project\Project */

$this->title = $model->project_nm;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('../project/_viewtoolbar', ['model' => $model]); ?>

<?php 
$is_maint = ($model->is_maint == 'T');
?>

<table class="hundred-pct table">
<tr><td class="sixty-pct datatop">
    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
    	'attributes' => [
            [
            	'attribute' => 'project_status', 
            	'value' => Html::encode($model->statusText),
            	'contentOptions' => $model->project_status == 'A' ? ['class' => 'success'] : ($model->project_status == 'X' ? ['class' => 'danger'] : ['class' => 'default']),
            ],
            'project_id',
            'addressTexts:ntext',
    		'general_contractor',
            ['attribute' => 'agreement_type', 'value' => Html::encode($model->agreementType->descrip)],
            [
            	'attribute' => 'disposition', 
            	'format' => 'raw',
            	'value' => $model->disposition == 'A' ? 
            		'<span class="text-success">Approved</span>' :
            		'<span class="text-danger">Denied</span>',
            ],
            [
            	'attribute' => 'is_maint',
            	'format' => 'raw',
            	'label' => 'Job Type',
            	'value' => $is_maint ? '<span class="label label-warning">Maintenance</span>'
	        						 : 'Project',
            ],
            [
            	'label' => 'Start Date',
            	'format' => 'date',
            	'value' => isset($model->awarded) ? $model->awarded->start_dt : null,
            ],
            'close_dt:date',
        ],
    ]) ?>
    
    <?php 
	$specialColumns = [
			[
				'attribute' => 'hourRange',
				'label' => 'Hours',
				'hAlign' => 'right',
			],
			[
				'attribute' => 'amountRange',
				'label' => 'Estimate',
				'hAlign' => 'right',
			],
	];    
    
    echo $this->render('../project/_registrationgrid', [
    		'registrationProvider' => $registrationProvider,
    		'model' => $model,
    		'is_maint' => $is_maint,
    		'controller' => 'registration-lma',
    		'specialColumns' => $specialColumns,
     ]);
	
    ?>
    
    
    </td><td class="forty-pct datatop">

  <?=  $this->render('../partials/_journal', ['model' => $model, 'noteModel' => $noteModel]) ?>

</td></tr></table>
    
</div>
<?= $this->render('../partials/_modal') ?>