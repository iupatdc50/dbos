<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\value\BillRateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Bill Rates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bill-rate-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Bill Rate', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            'lob_cd',
            ['attribute' => 'member_class', 'value' => 'memberClass.descrip'],
            ['attribute' => 'rate_class', 'value' => 'rateClass.descrip', 'contentOptions' => ['style' => 'white-space: nowrap;']],
            ['attribute' => 'fee_type', 'value' => 'feeType.descrip', 'contentOptions' => ['style' => 'white-space: nowrap;']],
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
