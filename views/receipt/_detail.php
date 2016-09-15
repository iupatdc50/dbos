<?php

use yii\helpers\Html;
use kartik\detail\DetailView;

?>

<div class="receipt-detail">

    <?= DetailView::widget([
        'model' => $modelReceipt,
        'attributes' => [
            'received_dt:date',
        	[
            		'attribute' => 'payor_nm',
            		'value' => $modelReceipt->payor_nm . ($modelReceipt->payor_type == 'O' ? ' (for ' . $modelReceipt->responsible->employer->contractor . ')' : ''),
    		],
            [
            		'attribute' => 'payment_method',
            		'value' => Html::encode($modelReceipt->methodText) . ($modelReceipt->payment_method != '1' ? ' [' . $modelReceipt->tracking_nbr . ']' : ''),
   			],
            'received_amt',
        	'unallocated_amt',
        	'helperDuesText',
        	'feeTypeTexts:ntext',
        	'remarks:ntext',
        ],
    ]) ?>


</div>