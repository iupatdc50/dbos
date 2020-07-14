<?php

use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\ContributionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $lobPicklist array */

$this->title = 'Contribution Rates';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contribution-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

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
    						'content' => Html::button('Create Contribution Rate', [
    						        'class' => 'btn btn-success btn-modal',
    						        'value' => Url::to(["create"]),
                                    'id' => 'ContributionCreateButton',
                                    'data-title' => 'Contribution Rate',
                            ]),
		],
        'columns' => [
        	[
                'class' => 'kartik\grid\DataColumn',
        		'attribute' => 'lob_cd',
        		'filterType' => GridView::FILTER_SELECT2,
        		'filter' => $lobPicklist,
        		'filterWidgetOptions' => [
        						'size' => Select2::SMALL,
        						'hideSearch' => true,
        						'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
        		],
    		],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'contrib_type',
                'value' => 'feeType.descrip',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => array_merge(["" => ""], $searchModel->contribOptions),
                'filterWidgetOptions' => [
                    'size' => Select2::SMALL,
                    'hideSearch' => true,
                    'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
                ],
            ],
            [
                'attribute' => 'wage_pct',
                'contentOptions' => ['style' => 'text-align:right',]
            ],
            [
                'attribute' => 'factor',
                'contentOptions' => ['style' => 'text-align:right',]
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'operand',
                'value' => 'operandText',
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => array_merge(["" => ""], $searchModel->operandOptions),
                'filterWidgetOptions' => [
                    'size' => Select2::SMALL,
                    'hideSearch' => true,
                    'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
                ],
            ],


            ['class' => 'yii\grid\ActionColumn', 'template' => '{update} {delete}',],
        ],
    ]); ?>

</div>
<?= $this->render('../partials/_modal') ?>


