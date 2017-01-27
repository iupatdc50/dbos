<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use app\helpers\OptionHelper;
use kartik\checkbox\CheckboxX;

/* @var $this yii\web\View */
/* @var $address app\models\base\BaseAddress */
/* @var $form kartik\form\ActiveForm */
/* @var $addressForm boolean If this variable exists, then these fields are displayed as part of
 *                           a standalone address form
 */
?>

<div class="address-fields">

	<?php if (isset($addressForm)): ?>
    <?= $form->field($address, 'address_type')->widget(Select2::className(), [
    		'data' => $address->addressTypeOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Address Type...'],
    		
    ]) ?>
    <?php else: ?>
    <?= $form->field($address, 'address_type')->hiddenInput()->label(false); ?>
    <?php endif; ?>

    <?= $form->field($address, 'address_ln1')->textInput(['maxlength' => 50])->label('Address Line 1') ?>

    <?= $form->field($address, 'address_ln2')->textInput(['maxlength' => 50])->label('Address Line 2') ?>

    <?= $form->field($address, 'zip_cd')->textInput(['maxlength' => 5])->label('Zip Code') ?>
    
    <div class="form-group generated-city-ln">
    	<label class="control-label col-sm-3" for="city-ln"></label>
    	<div id="city-ln" class="col-sm-6"></div>
    </div>
    
    <?php if (!$address->isDefault): ?>
    <br />
    
    <?= $form->field($address, 'set_as_default')->widget(CheckboxX::className(), ['pluginOptions' => ['threeState' => false]]) ?>
    
    <?php endif; ?>

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