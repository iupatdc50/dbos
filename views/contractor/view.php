<?php

use yii\helpers\Html;
use kartik\detail\DetailView;
use yii\jui\Accordion;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $employeeProvider yii\data\ActiveDataProvider */
/* @var $employeeSearchModel app\models\member\MemberSearch */
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
	        [
	        	'attribute' => 'is_active', 
	        	'value' => Html::encode($model->statusText),
	        	'rowOptions' => ($model->is_active == 'T') ? ['class' => 'success'] : ['class' => 'default'],
	        ],
	        'license_nbr',
            'contact_nm',
            'addressTexts:ntext',
            'phoneTexts:ntext',
            'email:email',
            'url:url',
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
   		<?= $this->render('_employee', ['provider' => $employeeProvider, 'searchModel' => $employeeSearchModel]); ?> 	
	</div>
</div>
<?= $this->render('../partials/_modal') ?>

