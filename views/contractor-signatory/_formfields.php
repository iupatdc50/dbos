<?php

use kartik\form\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $modelSig app\models\contractor\Signatory */
/* @var $form kartik\form\ActiveForm */
?>

<div class="signatory-fields">

    <?= $form->field($modelSig, 'lob_cd')->widget(Select2::className(), [
    		'data' => $modelSig->lobOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Local...'],
    ]) ?>

    <?= $form->field($modelSig, 'is_pla')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ])  
    ?>

    <?= $form->field($modelSig, 'assoc')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($modelSig, 'signed_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <?php if (!$modelSig->isNewRecord): ?>
	    <?= $form->field($modelSig, 'term_dt')->widget(DateControl::className(), [
	    		'type' => DateControl::FORMAT_DATE,
	    ])  ?>
    <?php endif; ?>
    
    <?= $form->field($modelSig, 'doc_file')->widget(FileInput::className(), [
    		'options' => ['accept' => 'application/pdf'],
    		'pluginOptions'=> [
    				'allowedFileExtensions'=>['pdf','png'],
    				'showUpload' => false,
    		],
    ]); ?>
    

</div>
