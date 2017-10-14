<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\ReceiptContractor */
/* @var $form yii\widgets\ActiveForm */


?>

<div class="receipt-formfields">

	<?= $form->field($model, 'received_dt')->widget(DateControl::className(), [
			'type' => DateControl::FORMAT_DATE,
	])   ?>
       
    <?= $form->field($model, 'acct_month')->widget(Select2::className(), [
    		'data' => $model->acctMonthOptions,
    		'options' => ['placeholder' => 'Select month...'],
    ]) ?>
	
	<?= $form->field($model, 'payor_nm')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'payment_method')->widget(Select2::className(), [
    		'data' => $model->methodOptions,
    		'options' => ['placeholder' => 'Select...', 'id' => 'paymentmethod'],
    ]) ?>
    <?= $form->field($model, 'tracking_nbr')
    		 ->textInput(['maxlength' => true, 'id' => 'trackingnbr'])
    		 ->label('', ['id' => 'trackinglbl']) ?>
    
    <?= $form->field($model, 'received_amt')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 3]) ?>

</div>

<?php 
$script = <<< JS

$(function() {
		$('#trackinglbl').hide();
		$('#trackingnbr').hide();
})

$('#paymentmethod').change(function() {
	var paymethod = $(this).val();
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
});

JS;
$this->registerJs($script);
?>