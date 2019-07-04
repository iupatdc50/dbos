<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Respirator Brands';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="resp-brand-index">


    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'dataProvider' => $dataProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_WARNING,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Respirator Brand', ['create'], ['class' => 'btn btn-success']),
		],
    	'columns' => [

            'brand',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
