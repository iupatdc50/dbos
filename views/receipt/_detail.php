<?php

use app\models\user\User;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;
use app\helpers\OptionHelper;
// use kartik\detail\DetailView;

/* @var $modelReceipt app\models\accounting\Receipt */
/* @var $this View */

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
            		'value' => $modelReceipt->payor_nm,
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
	$created_by_nm = $modelReceipt->createdBy->id == User::USER_PORTAL ? 'Member (Online)' : $modelReceipt->createdBy->username;
	$blameable_attributes = [
        [
            'attribute' => 'created_by',
            'value' =>  $created_by_nm . ' on ' . date('m/d/Y h:i a', $modelReceipt->created_at),
        ],
        [
            'attribute' => 'updated_by',
            'value' => isset($modelReceipt->updatedBy) && ($modelReceipt->created_at != $modelReceipt->updated_at) ? $modelReceipt->updatedBy->username . ' on ' . date('m/d/Y h:i a', $modelReceipt->updated_at) : null,
            'visible' => isset($modelReceipt->updatedBy) && ($modelReceipt->created_at != $modelReceipt->updated_at),
        ]
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
                $blameable_attributes
            ),
        ]);
    } catch (Exception $e) {
    } ?>


</div>