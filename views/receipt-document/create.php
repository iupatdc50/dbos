<?php /** @noinspection PhpUnhandledExceptionInspection */

use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\Document */
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
        'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'doc_file')->widget(FileInput::className(), [
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
