<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Fee Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fee-type-index">


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'panel'=>[
	        'type'=>GridView::TYPE_WARNING,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Fee Type', ['create'], ['class' => 'btn btn-success']),
		],
    	'columns' => [

            'fee_type',
            'descrip',
            'freq',
    		'is_assess',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
