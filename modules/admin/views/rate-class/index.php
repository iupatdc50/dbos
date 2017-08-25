<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\value\RateClassSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lobPicklist array */

$this->title = 'Rate Classes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rate_class-index">

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
			'content' => Html::a('Create Rate Class', ['create'], ['class' => 'btn btn-success']),
		],
        'columns' => [
        	'rate_class',
        	'descrip',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update} {delete}',],
        ],
    ]); ?>

</div>
