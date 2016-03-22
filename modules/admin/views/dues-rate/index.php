<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\accounting\DuesRateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lobPicklist array */

$this->title = 'Dues Rates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dues-rate-index">

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
    						'content' => Html::a('Create Dues Rate', ['create'], ['class' => 'btn btn-success']),
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
            ['attribute' => 'rate_class', 'value' => 'rateClass.descrip', 'contentOptions' => ['style' => 'white-space: nowrap;']],
            'effective_dt:date',
            'end_dt:date',
            [
            	'attribute' => 'rate',
            	'contentOptions' => ['style' => 'text-align:right',]
    		],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}',],
        ],
    ]); ?>

</div>
