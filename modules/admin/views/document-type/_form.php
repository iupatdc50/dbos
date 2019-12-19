<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\value\DocumentType */
/* @var $form kartik\form\ActiveForm */
?>

<div class="document_type-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?= $form->field($model, 'doc_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'catg')->widget(Select2::className(), [
    		'data' => $model->catgOptions,
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Catgegory...'],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
