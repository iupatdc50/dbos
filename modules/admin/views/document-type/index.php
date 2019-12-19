<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\value\DocumentTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Document Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-type-index">

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_WARNING,
	        'heading'=> $this->title,
		    'after' => false,
		],
		'toolbar' => [
			'content' => Html::a('Create Document Type', ['create'], ['class' => 'btn btn-success']),
		],
        'columns' => [
            'doc_type',
    		[
                'class' => 'kartik\grid\DataColumn',
				'attribute' => 'catg',
            	'filterType' => GridView::FILTER_SELECT2,
            	'filter' => $searchModel->catgOptions,
            	'filterWidgetOptions' => [
            			'size' => Select2::SMALL,
            			'hideSearch' => true,
            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
            	],
    		],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
