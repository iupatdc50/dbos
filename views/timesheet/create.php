<?php

use kartik\select2\Select2;
use kartik\widgets\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $modelTimesheet app\models\training\Timesheet */
/* @var $modelHours yii\base\DynamicModel */
/* @var $processes array */

?>

<div class="timesheet-formfields">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'id' => 'allocation-add',
        'enableClientValidation' => true,
        'fieldConfig' => [
                'horizontalCssClasses' => [
                    'label' => 'col-md-4',
                    'wrapper' => 'col-md-7',
                ],
        ],
    ]); ?>

    <?= $form->field($modelTimesheet, 'acct_month')->widget(Select2::className(), [
        'size' => Select2::SMALL,
        'data' => $modelTimesheet->acctMonthOptions,
        'options' => ['placeholder' => 'Select month...'],
    ]) ?>

    <hr>

    <?php
        foreach ($processes as $process) {
            echo $form->field($modelHours, $process->id)->textInput(['maxlength' => true, 'style' => 'width:100px'])->label($process->work_process);
        }

    ?>

    <hr>

    <?= $form->field($modelTimesheet, 'total_hours')->textInput(['maxlength' => true, 'style' => 'width:100px']) ?>

    <?= $form->field($modelTimesheet, "doc_file")->widget(FileInput::className(), [
        'options' => ['accept' => 'application/pdf'],
        'pluginOptions'=> [
            'allowedFileExtensions'=>['pdf','png'],
            'showUpload' => false,
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Create', ['id' => 'createbtn', 'class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$script = <<< JS

$(document).keydown(function(e) {

  // Set self as the current item in focus
  var self = $(':focus'),
      form = self.parents('form:eq(0)'),
      next,
      focusable;

  focusable = $('div.timesheet-formfields').find('input,a,select,button,textarea').filter(':visible');

  if (e.which === 13) {
      next = focusable.eq(focusable.index(self) + 1);
      if (next.length) {
          next.focus();
      } else {
          form.submit();
      }
      return false;
  }
});

JS;
$this->registerJs($script);

?>

