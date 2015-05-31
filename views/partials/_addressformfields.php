<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $address app\models\base\BaseAddress */
/* @var $form kartik\form\ActiveForm */
?>

<div class="address-fields">

    <?= $form->field($address, 'address_type')->widget(Select2::className(), [
    		'data' => $address->addressTypeOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Address Type...'],
    ]) ?>

    <?= $form->field($address, 'address_ln1')->textInput(['maxlength' => 30])->label('Address Line 1') ?>

    <?= $form->field($address, 'address_ln2')->textInput(['maxlength' => 30])->label('Address Line 2') ?>

    <?= $form->field($address, 'zip_cd')->textInput(['maxlength' => 5])->label('Zip Code') ?>
    
    <div class="form-group generated-city-ln">
    	<label class="control-label col-sm-3" for="city-ln"></label>
    	<div id="city-ln" class="col-sm-6"></div>
    </div>

</div>

<?php 
$script = <<< JS

$('#address-zip_cd').change(function() {
	var zip_cd = $(this).val();
	$.get('/zip-code/get-city-ln', { zip_cd : zip_cd }, function(data) {
		$('#city-ln').html($.parseJSON(data));
	});
});

JS;
$this->registerJs($script);
?>