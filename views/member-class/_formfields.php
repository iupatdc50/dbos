<?php

use kartik\widgets\Select2;
use kartik\widgets\FileInput;

/* @var $modelClass app\models\member\MemberClass */

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

<?= $form->field($modelClass, "doc_file")->widget(FileInput::className(), [
    'options' => ['accept' => 'application/pdf'],
    'pluginOptions'=> [
        'allowedFileExtensions'=>['pdf','png'],
        'showUpload' => false,
    ],
]); ?>

<?php
$script = <<< JS

$(function() {
		$('#wagepctlbl').hide();
		$('#wagepct').hide();
})

$('#mclass').change(function() {
	var classid = $(this).val();
	if(classid == 'AR' || classid == 'MR') {
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