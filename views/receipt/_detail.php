<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use app\helpers\OptionHelper;
// use kartik\detail\DetailView;

/* @var $modelReceipt app\models\accounting\Receipt */

?>

<div class="receipt-detail">

	<?php
	$common_attributes = [
            'received_dt:date',
			[
					'attribute' => 'acct_month',
					'value' => $modelReceipt->acctMonthText,
			],
        	[
            		'attribute' => 'payor_nm',
            		'value' => $modelReceipt->payor_nm . ($modelReceipt->payor_type == 'O' ? ' (for ' . $modelReceipt->responsible->employer->contractor . ')' : ''),
    		],
            [
            		'attribute' => 'payment_method',
            		'value' => Html::encode($modelReceipt->methodText) . ($modelReceipt->payment_method != '1' ? ' [' . $modelReceipt->tracking_nbr . ']' : ''),
   			],
            [
            		'attribute' => 'received_amt',
            		'value' => $modelReceipt->void == OptionHelper::TF_TRUE ? '** VOID **' : $modelReceipt->received_amt,
            ],
        	'unallocated_amt',
        	'remarks:ntext',
        ]; 
	?>

    <?php
    try {
        echo DetailView::widget([
            'model' => $modelReceipt,
            'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
            'attributes' => array_merge(
                $common_attributes,
                $modelReceipt->customAttributes,
                [[
                    'attribute' => 'created_by',
                    'value' => $modelReceipt->createdBy->username,
                ]]
            ),
        ]);
    } catch (Exception $e) {
    } ?>


</div>