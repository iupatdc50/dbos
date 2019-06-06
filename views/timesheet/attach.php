<?php

use yii\bootstrap\ActiveForm;
use kartik\widgets\FileInput;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\training\Timesheet */
?>

<div class="attachment-form">

    <?php
    $form = ActiveForm::begin([
        'id' => 'attachment-form',
        'enableClientValidation' => true,
        'options' => ['enctype' => 'multipart/form-data'],
    ]);

    echo $form->field($model, "doc_file")->widget(FileInput::className(), [
        'options' => ['accept' => 'application/pdf'],
        'pluginOptions' => [
            'allowedFileExtensions' => ['pdf', 'png'],
        ],
    ]);

    ActiveForm::end();

    ?>

</div>