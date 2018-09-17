<?php

use yii\helpers\Html;

/* @var $model app\models\accounting\Receipt */
/* @var $allocProvider yii\data\ActiveDataProvider */


	$common_attributes = [
        	[
            		'attribute' => 'payor_nm',
            		'value' => $model->payor_nm . ($model->payor_type == 'O' ? ' (for ' . $model->responsible->employer->contractor . ')' : ''),
    		],
            [
            		'attribute' => 'payment_method',
            		'value' => Html::encode($model->methodText) . ($model->payment_method != '1' ? ' [' . $model->tracking_nbr . ']' : ''),
   			],
        	'unallocated_amt',
        	'remarks:ntext',
        ];
?>

<?php if ($model->payor_type == 'M') : ?>

<div class="top-half">
    <?= $this->render('_printcontent', [
        'model' => $model,
        'allocProvider' => $allocProvider,
        'common_attributes' => $common_attributes,
    ]); ?>

</div>

<?php endif; ?>

<br />

<div>
  <?= $this->render('_printcontent', [
          'model' => $model,
          'allocProvider' => $allocProvider,
          'common_attributes' => $common_attributes,
  ]); ?>

</div>


