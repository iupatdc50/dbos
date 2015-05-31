<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ZipCodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Zip Codes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zip-code-index">

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
			'content' => Html::a('Create Zip Code', ['create'], ['class' => 'btn btn-success']),
		],
        'columns' => [

            'zip_cd',
            'city',
            'island',
            'st',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
