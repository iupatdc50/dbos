<?php

/* @var $allocProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $modelReceipt \app\models\accounting\ReceiptMember */

use yii\helpers\Html;

$this->title = 'Build Member Receipt ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Member Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $modelReceipt->id;

?>
<div class="receipt-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="leftside sixty-pct">
    	<?= $this->render('../receipt/_updatetoolbar', ['modelReceipt' => $modelReceipt]); ?>
    	<?= $this->render('../receipt/_detail', ['modelReceipt' => $modelReceipt]); ?>
    </div>
    
    
    <div class="rightside thirtyfive-pct">
        <?= $this->render('../receipt/_allocgrid', [
            'allocProvider' => $allocProvider,
            'alloc_memb_id' => $modelReceipt->allocatedMembers[0]->id,
        ]); ?>
    </div>
    
        
    
</div>
