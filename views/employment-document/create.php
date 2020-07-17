<?php /** @noinspection PhpUnhandledExceptionInspection */

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\employment\Document */
/* @var $form kartik\form\ActiveForm */

?>
<div class="document-create">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => 'document-form',
        'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'doc_type')->widget(Select2::className(), [
        'data' => $model->typeOptions,
        'hideSearch' => true,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Select...', 'id' => 'docType'],
    ]) ?>

    <div id="testresult">
        <?= $form->field($model, 'test_result')->widget(Select2::className(), [
            'data' => $model->getResultOptions(),
            'hideSearch' => true,
            'size' => Select2::SMALL,
            'options' => ['placeholder' => 'Select...', 'id' => 'result'],
        ]) ?>
    </div>

    <?= $form->field($model, 'doc_file')->widget(FileInput::className(), [
//    		'options' => ['accept' => 'application/pdf'],
        'pluginOptions'=> [
            'allowedFileExtensions'=>['pdf','png', 'jpg', 'jpeg'],
            'showUpload' => false,
        ],
    ]); ?>



    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(function() {
    toggle($('#docType').val());
});

$('#docType').change(function() {
    toggle($(this).val());
});

function toggle(docType) {
    const drugopt = $('#testresult');
    if(docType == 'Drug Test') {
        drugopt.show();
    } else {
        drugopt.hide();
    }
}

JS;
$this->registerJs($script);
?>

