<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\TradeFeeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lobPicklist array */
/* @var $feetypePicklist array */

$this->title = 'Trade Fees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trade-fee-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_WARNING,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Trade Fee', ['create'], ['class' => 'btn btn-success']),
		],
        'columns' => [
        	[
        		'attribute' => 'lob_cd',
        		'filterType' => GridView::FILTER_SELECT2,
        		'filter' => $lobPicklist,
        		'filterWidgetOptions' => [
        						'size' => \kartik\widgets\Select2::SMALL,
        						'hideSearch' => true,
        						'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
        		],
    		],
            [
            		'attribute' => 'fee_type', 
	        		'filterType' => GridView::FILTER_SELECT2,
	        		'filter' => $feetypePicklist,
	        		'filterWidgetOptions' => [
	        						'size' => \kartik\widgets\Select2::SMALL,
	        						'hideSearch' => true,
	        						'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
	        		],
            		'value' => 'feeType.descrip', 
            		'contentOptions' => ['style' => 'white-space: nowrap;']
            		
        	],
        	[
        		'attribute' => 	'employer_remittable',
        		'class' => 'kartik\grid\BooleanColumn',
        		'value' => function($data) {return ($data->employer_remittable) == 'T' ? true : false;},
	        ],
	        [
	        	'attribute' => 	'member_remittable',
	        	'class' => 'kartik\grid\BooleanColumn',
        		'value' => function($data) {return ($data->member_remittable) == 'T' ? true : false;},
	        ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
