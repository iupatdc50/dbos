<?php

use app\helpers\OptionHelper;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\project\lma\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'LMA Projects';
$this->params['breadcrumbs'][] = $this->title;

$specialColumns = [
		[
				'attribute' => 'is_maint',
    			'width' => '90px',
				'value' => 'maintText',
				'filterType' => GridView::FILTER_SELECT2,
				'filter' => array_merge(["" => ""], OptionHelper::getTFOptions()),
				'filterWidgetOptions' => [
            			'size' => \kartik\widgets\Select2::SMALL,
						'hideSearch' => true,
						'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
				],
				
		],
];

?>
<div class="project-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= $this->render('../project/_maingrid', [
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
    	'heading' => $this->title,
    	'specialColumns' => $specialColumns,
    ]); ?>

</div>
