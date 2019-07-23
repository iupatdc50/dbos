<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
// use kartik\detail\DetailView;
use yii\jui\Accordion;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $employeeProvider yii\data\ActiveDataProvider */
/* @var $employeeSearchModel app\models\member\MemberSearch */
/* @var $signatoryModel app\models\contractor\Signatory */
/* @var $noteModel app\models\contractor\Note */

$this->title = $model->contractor;
$this->params['breadcrumbs'][] = ['label' => 'Contractors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contractor-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="sixty-pct">
		<?= $this->render('../partials/_quicksearch', ['className' => 'contractor']); ?>
    	<div><p>
        	<?php if(Yii::$app->user->can('updateContractor')): ?>
				<?= Html::a('Update', ['update', 'id' => $model->license_nbr], ['class' => 'btn btn-primary']) ?>
				<?php if(Yii::$app->user->can('deleteContractor')) :?>
			        <?= Html::a('Delete', ['delete', 'id' => $model->license_nbr], [
			            'class' => 'btn btn-danger',
			            'data' => [
			                'confirm' => 'Are you sure you want to delete this item?',
			                'method' => 'post',
			            ],
			        ]) ?>
			    <?php endif; ?> 
			<?php endif; ?>
    	</p></div>
	</div>
   	<div class= "leftside sixty-pct" >
    <?= /** @noinspection PhpUnhandledExceptionInspection */
    DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
        'attributes' => [
	        [
	        	'attribute' => 'is_active', 
	        	'value' => Html::encode($model->statusText),
	        	'contentOptions' => ($model->is_active == 'T') ? ['class' => 'success'] : ['class' => 'default'],
	        ],
	        'license_nbr',
            'contact_nm',
            'addressTexts:ntext',
            'phoneTexts:ntext',
            'emailTexts:ntext',
            'url:url',
        	[
        		'attribute' => 'deducts_dues',
        		'value' => Html::encode($model->deductsDuesText),
        	],
        ],
    ]) ?>
    
<?php
	$signatory = isset($model->currentSignatory) ? $model->currentSignatory->lobs : 'Non-Union';
 	$signatoryUrl = Yii::$app->urlManager->createUrl(['contractor-signatory/summary-json', 'id' => $model->license_nbr]);
 	$ancillaryUrl = Yii::$app->urlManager->createUrl(['contractor-ancillary/summary-json', 'id' => $model->license_nbr]);
    $billsUrl = Yii::$app->urlManager->createUrl(['contractor-bill/summary-json', 'id' => $model->license_nbr]);
 	$receiptsUrl = Yii::$app->urlManager->createUrl(['receipt-contractor/summ-flattened-json', 'license_nbr' => $model->license_nbr]);
 	
 	$registrationUrl = Yii::$app->urlManager->createUrl(['registration/summary-json', 'id' => $model->license_nbr]);
 	
?>


<?=
/** @noinspection PhpUnhandledExceptionInspection */
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
                'content' => '<div data-url='.$billsUrl.'>loading...</div>',
			],
			[
				'header' => Html::tag('span', 'Receipt History'),
				'content' => '<div data-url='.$receiptsUrl.'>loading...</div>',
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
	    <hr>

        <?php if(Yii::$app->user->can('contractorJournal')):?>

        <div id="journal">
			<?php if ($model->noteCount >= 1): ?>
		     	<p> <?= $model->noteCount > 1 ? $model->noteCount . ' Journal Notes' : 'One Journal Note'; ?></p>
		     	<?= $this->render('../partials/_notes', ['notes' => $model->notes, 'controller' => 'contractor-note']); ?>
		     <?php endif; ?>
		
			<?=
                /** @noinspection RequireParameterInspection */
                $this->render('../partials/_noteform', ['model' => $noteModel])
            ?>
		
		</div>

        <?php endif; ?>
   		
	</div>
   	<div class="rightside thirtyfive-pct"> 
   		<?= $this->render('_employee', ['provider' => $employeeProvider, 'searchModel' => $employeeSearchModel]); ?> 	

    </div>
</div>
<?= $this->render('../partials/_modal') ?>

