<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\accounting\Receipt;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\ReceiptContractor */
/* @var $form kartik\widgets\ActiveForm */


?>

<div class="receipt-formfields">

    <?php if ((!$model->isNewRecord)  && ($model->payor_type == Receipt::PAYOR_CONTRACTOR)): ?>

    <?= $form->field($model, 'payor_nm', [
        'addon' => [
            'append' => [
                'content' => Html::button('<i class="glyphicon glyphicon-transfer"></i>&nbsp;Change Employer', [
                    'value' => Url::to(['/responsible-employer/update', 'id' => $model->id]),
                    'class' => 'btn btn-default btn-modal',
                    'data-title' => 'Reassign',
                    'title' => Yii::t('app', 'Change Employer'),
                ]),
                'asButton' => true
            ]
        ]
    ]) ?>

    <?php else: ?>

    <?= $form->field($model, 'payor_nm')->textInput(['maxlength' => true])->input('payor_nm', ['placeholder' => "(Optional)"]) ?>

    <? endif; ?>

    <?= $form->field($model, 'received_dt')->widget(DateControl::className(), [
			'type' => DateControl::FORMAT_DATE,
	])   ?>
       
    <?= $form->field($model, 'acct_month')->widget(Select2::className(), [
    		'data' => $model->acctMonthOptions,
    		'options' => ['placeholder' => 'Select month...'],
    ]) ?>
	
    <?= $form->field($model, 'payment_method')->widget(Select2::className(), [
    		'data' => $model->methodOptions,
    		'options' => ['placeholder' => 'Select...', 'id' => 'paymentmethod'],
    ]) ?>
    <?= $form->field($model, 'tracking_nbr')
    		 ->textInput(['maxlength' => true, 'id' => 'trackingnbr'])
    		 ->label('', ['id' => 'trackinglbl']) ?>
    
    <?= $form->field($model, 'received_amt')->textInput(['maxlength' => true, 'readonly' => (!$model->isNewRecord)]) ?>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 3]) ?>

</div>

<?php 
$script = <<< JS

$(function() {
    toggletracking($('#paymentmethod').val());
});

$('#paymentmethod').change(function() {
    toggletracking($(this).val());
});

function toggletracking(paymethod) {
	if(paymethod > 1) {
		$('#trackinglbl').show();
		$('#trackingnbr').show();
		if(paymethod == 2) {
			$('#trackinglbl').text('Check Number');
		} else if(paymethod == 3) {
			$('#trackinglbl').text('Auth Code');
		} 
	} else {
		$('#trackinglbl').hide();
		$('#trackingnbr').hide();
	};
    
}

JS;
$this->registerJs($script);
?>