<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Ancillary */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ancillary-form">

    <?php $form = ActiveForm::begin([
    		'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
    		'type' => ActiveForm::TYPE_HORIZONTAL,
    		'id' => 'ancillary-form',
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'agreement_type')->widget(Select2::className(), [
    		'data' => $model->typeOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Agreement Type...'],
    ]) ?>

    <?= $form->field($model, 'signed_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <?= $form->field($model, 'term_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <?= $form->field($model, 'doc_file')->widget(FileInput::className(), [
    		'options' => ['accept' => 'application/pdf'],
    		'pluginOptions'=> [
    				'allowedFileExtensions'=>['pdf','png'],
    				'showUpload' => false,
    		],
    ]); ?>
        
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
