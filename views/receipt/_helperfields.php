<?php

/* @var $model app\models\accounting\ReceiptContractor */
/* @var $form kartik\form\ActiveForm */

?>

<?= $form->field($model, 'helper_dues')->textInput(['maxlength' => true, 'id' => 'helperdues']) ?>

<?= $form->field($model, 'helper_hrs')
            ->textInput(['maxlength' => true, 'id' => 'helperhrs'])
            ->label('Hours', ['id' => 'helperhrslbl']) ?>

<?php
$script = <<< JS

$(function() {
		$('#helperhrslbl').hide();
		$('#helperhrs').hide();
});

$('#helperdues').change(function() {
	var dues = $(this).val();
	if(dues > 0.00) {
		$('#helperhrslbl').show();
		$('#helperhrs').show();
		$('#helperhrs').focus();
	} else {
		$('#helperhrslbl').hide();
		$('#helperhrs').hide();
	};
});

JS;
$this->registerJs($script);
?>

