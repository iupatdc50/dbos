<?php

use yii\helpers\Html;
use kartik\widgets\Select2;

?>

<?= $form->field($modelClass, 'class_id')->widget(Select2::className(), [
		'data' => $modelClass->classOptions,
		'hideSearch' => false,
		'size' => Select2::SMALL,
		'options' => ['placeholder' => 'Select...', 'id' => 'mclass'],
]); ?>
<?= $form->field($modelClass, 'wage_percent')
    			 ->textInput(['maxlength' => true, 'id' => 'wagepct']) 
    			 ->label('Percent', ['id' => 'wagepctlbl'])
?>

<?php 
$script = <<< JS

$(function() {
		$('#wagepctlbl').hide();
		$('#wagepct').hide();
})

$('#mclass').change(function() {
	var classid = $(this).val();
	if(classid == 'AR') {
		$('#wagepctlbl').show();
		$('#wagepct').show();
	} else {
		$('#wagepctlbl').hide();
		$('#wagepct').hide();
	};
});

JS;
$this->registerJs($script);
?>