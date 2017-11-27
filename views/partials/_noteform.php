<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\base\BaseNote */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="note-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'note')->textarea(['rows' => 3])->label(false) ?>
    
    <?php if(!isset($bypass_doc)): ?>
    <?= $form->field($model, 'doc_file')->widget(FileInput::className(), [
    		'options' => ['accept' => 'application/pdf'],
    		'pluginOptions'=> [
    				'allowedFileExtensions'=>['pdf','png'],
    				'showUpload' => false,
    		],
    ]); ?>
    <?php endif; ?>
    

    <div class="form-group">
        <?= Html::submitButton('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add Note', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
