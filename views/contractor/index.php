<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\contractor\ContractorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Contractors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contractor-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Contractor', ['create'], ['class' => 'btn btn-success']),
		],
        'rowOptions' => function($model) {
        					if(!isset($model->currentSignatory)) {
        						return ['class' => 'warning'];
        					}
    					},
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

    		[
    			'attribute' => 'lobs',
    			'value' => 'currentSignatory.lobs',
    			'label' => 'Union(s)',
    		],
            'license_nbr',
            [
            	'attribute' => 'contractor',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
            'contact_nm',
            'email:email',
            [
            	'attribute' => 'employeeCount',
            	'contentOptions' => function($model) {
			        					if($model->employeeCount == 0) {
			        						return ['class' => 'right zero'];
			        					} else {
			        						return ['class' => 'right'];
			        					}
			    					},
            ],
//            'url:url',

            [
            	'class' => 'yii\grid\ActionColumn',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
        ],
    ]); ?>

</div>
