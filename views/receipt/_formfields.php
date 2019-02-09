<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\ReceiptContractor */
/* @var $form kartik\widgets\ActiveForm */
/* @var $opt string */

?>

<div class="receipt-formfields">

    <?php if ($model->isNewRecord): ?>

    <?= $form->field($model, 'payor_nm')->textInput(['maxlength' => true])->input('payor_nm', ['placeholder' => $opt]) ?>

    <?php endif; ?>

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
    var lbl = $('#trackinglbl');
    var nbr = $('#trackingnbr');
	if(paymethod > 1) {
		lbl.show();
		nbr.show();
		if(paymethod == 2) {
			lbl.text('Check Number');
		} else if(paymethod == 3) {
			lbl.text('Auth Code');
		} 
	} else {
		lbl.hide();
		nbr.hide();
	}
    
}

JS;
$this->registerJs($script);
?>

