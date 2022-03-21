<?php

use app\models\accounting\ReceiptMember;
use yii\helpers\Html;
use kartik\grid\GridView;
use app\helpers\OptionHelper;
use yii\web\View;

/* @var $this View */
/* @var $model ReceiptMember */
/* @var $allocProvider yii\data\ActiveDataProvider */



$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if ($model->void == OptionHelper::TF_TRUE)
	$receipt_for = ' <span class="lbl-danger">** VOID **</span>';
elseif (isset($model->payingMember)) {
	$member_name = $model->payingMember->fullName;
	$member_id = $model->payingMember->member_id;
	$receipt_for = ' for ' . Html::a($member_name, ['/member/view', 'id' => $member_id]);
} else 
	$receipt_for = ' <span class="lbl-danger">Incomplete Receipt</span>';

?>
<div class="receipt-view">

	<h1><?= Html::encode('Receipt: ' . $this->title)  . $receipt_for ?></h1>
    
    <div class="leftside sixty-pct">
    	<?= $this->render('../receipt/_viewtoolbar', ['model' => $model]); ?>
    	<?= $this->render('../receipt/_detail', ['modelReceipt' => $model]); ?>
    </div>
    
    
    <div class="rightside thirtyfive-pct">
    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
    	'id' => 'alloc-grid',
        'dataProvider' => $allocProvider,		
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=> '<i class="glyphicon glyphicon-tasks"></i>&nbsp;Receipt Allocations',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
        'columns' => [
        		[
        				'class'=>'kartik\grid\ExpandRowColumn',
        				'width'=>'50px',
        				'value'=>function (/** @noinspection PhpUnusedParameterInspection */
        				                    $model, $key, $index, $column) {
        							return ($model['fee_type'] == 'DU') ? GridView::ROW_COLLAPSED : '';
        				        },
        				'detailUrl'=> Yii::$app->urlManager->createUrl(['allocation/detail-ajax']),
        				'headerOptions'=>['class'=>'kartik-sheet-style'],
        				'expandOneOnly'=>true,
        		],
        		
        		'fee_type',
        		[
        		        'class' => 'kartik\grid\DataColumn',
        				'attribute' => 'allocation_amt',
        				'hAlign' => 'right',
        				'format' => ['decimal', 2],
        				
    			],
    	],
//    	'showPageSummary' => true,
        		
    ]);?>
    </div>
    
        
    
</div>

