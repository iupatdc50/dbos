<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Initiation Fees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="init-fee-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_WARNING,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Init Fee', ['create'], ['class' => 'btn btn-success']),
		],
    	'columns' => [

    		'lob_cd',
            [
            		'attribute' => 'member_class',
            		'value' => 'classCd.descrip',
            ],
            'effective_dt:date',
            'end_dt:date',
            'fee',
            'dues_months',
	        [
	        	'attribute' => 	'included',
	        	'class' => 'kartik\grid\BooleanColumn',
        		'value' => function($data) {return ($data->included) == 'T' ? true : false;},
	        ],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}',],
        ],
    ]); ?>

</div>
