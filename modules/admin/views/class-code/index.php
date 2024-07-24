<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\member\ClassCodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Member Class Codes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="class_code-index">

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
			'content' => Html::a('Create Class Code', ['create'], ['class' => 'btn btn-success']),
		],
        'columns' => [
            'member_class_cd',
        	'descrip',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}',],
        ],
    ]); ?>

</div>
