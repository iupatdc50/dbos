<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\value\TradeSpecialtySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lobPicklist array */

$this->title = 'Trade Specialties';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trade-specialty-index">

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
			'content' => Html::a('Create Trade Specialty', ['create'], ['class' => 'btn btn-success']),
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
        	'specialty',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
