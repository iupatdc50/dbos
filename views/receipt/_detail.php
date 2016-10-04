<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\detail\DetailView;

/* @var $modelReceipt app\models\accounting\Receipt */

?>

<div class="receipt-detail">

	<?php
	$common_attributes = [
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
        	'remarks:ntext',
        ]; 
	?>

    <?= DetailView::widget([
        'model' => $modelReceipt,
        'attributes' => array_merge($common_attributes, $modelReceipt->customAttributes),
    ]) ?>


</div>