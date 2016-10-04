<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use kartik\editable\Editable;


$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$member_name = $model->payingMember->fullName;
$member_id = $model->payingMember->member_id;

?>
<div class="receipt-view">

    <h1><?= Html::encode('Receipt: ' . $this->title . ' for ') . Html::a($member_name, ['/member/view', 'id' => $member_id]) ?></h1>
    
    <div class="leftside sixty-pct">
    	<?= $this->render('../receipt/_viewtoolbar', ['model' => $model, 'class' => 'member']); ?>
    	<?= $this->render('../receipt/_detail', ['modelReceipt' => $model]); ?>
    </div>
    
    
    <div class="rightside thirtyfive-pct">
    <?= GridView::widget([
    	'id' => 'itemize-grid',
        'dataProvider' => $allocProvider,		
		'panel'=>[
	        'type'=>GridView::TYPE_DEFAULT,
	        'heading'=> '<i class="glyphicon glyphicon-tasks"></i>&nbsp;Receipt Allocations',
				'before' => false,
				'after' => false,
				'footer' => false,
		],
        'columns' => [
        		'fee_type',
        		[
        				'attribute' => 'allocation_amt',
        				'hAlign' => 'right',
        				'format' => ['decimal', 2],
        				
    			],
    	],
//    	'showPageSummary' => true,
        		
    ]);?>
    </div>
    
        
    
</div>

