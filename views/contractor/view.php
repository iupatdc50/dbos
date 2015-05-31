<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\jui\Accordion;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $employeeProvider yii\data\ActiveDataProvider */
/* @var $signatoryModel app\models\contractor\Signatory */

$this->title = $model->contractor;
$this->params['breadcrumbs'][] = ['label' => 'Contractors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contractor-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->license_nbr], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->license_nbr], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

   <div class= "leftside sixty-pct" >
    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
        'attributes' => [
            'license_nbr',
            'contact_nm',
            'addressTexts:ntext',
            'phoneTexts:ntext',
            'email:email',
            'url:url',
            ['attribute' => 'pdca_member', 'value' => is_null($model->pdca_member) ? null : Html::encode($model->pdcaText)],
            'cba_dt:date',
        ],
    ]) ?>
    
<?php
	$signatory = isset($model->currentSignatory) ? $model->currentSignatory->lobs : 'Non-Union';
 	$signatoryUrl = Yii::$app->urlManager->createUrl(['contractor-signatory/summary-json', 'id' => $model->license_nbr]);
 	$ancillaryUrl = Yii::$app->urlManager->createUrl(['contractor-ancillary/summary-json', 'id' => $model->license_nbr]);
 	
 	
 	$registrationUrl = Yii::$app->urlManager->createUrl(['registration/summary-json', 'id' => $model->license_nbr]);
 	
?>
    
<?=
	Accordion::widget([
		'items' => [
			[
				'header' => Html::tag('span', 'Agreements: ') . $signatory ,
				'content' => '<div data-url='.$signatoryUrl.'>loading...</div><div data-url='.$ancillaryUrl.'></div>',
			],
			[
				'header' => Html::tag('span', 'Special Projects') ,
				'content' => '<div data-url='.$registrationUrl.'>loading...</div>',
			],
			[
				'header' => Html::tag('span', 'Billing History') ,
				'content' => 'Feature not supported',
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
    
   
   </div><div class="rightside thirtyfive-pct"> 
    <?= GridView::widget([
        'dataProvider' => $employeeProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=>'Current Employees',
	        'before' => false,
	        'after' => false,
	    ],
        'columns' => [
        	[
        		'label' => 'Name',
        		'format' => 'raw',
        		'value' => function($data) {
        			return Html::a(Html::encode($data->fullName), ['member/view', 'id' => $data->member_id]);
        		},
        	],
        	[
        		'attribute' => 'special',
        		'format' => 'raw',
        		'value' => function($data) {
	        		return (isset($data->worksFor) && ($data->worksFor->is_loaned == 'T')) ?
    	    			Html::tag('span', ' On Loan', ['class' => 'label label-success']) : '';
        		},
        	],
        ],
    ]) ?>
	</div>
</div>
<?= $this->render('../partials/_modal') ?>

