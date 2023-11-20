<?php

use kartik\select2\Select2;
use kartik\widgets\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $modelTimesheet app\models\training\Timesheet */
/* @var $modelHours yii\base\DynamicModel */
/* @var $processes array */

// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);
$init_contractor = empty($modelTimesheet->license_nbr) ? 'Search for a contractor...' : $modelTimesheet->contractor->contractor;

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
            echo $form->field($modelHours, $process->id)->textInput([
                    'maxlength' => true,
                    'class' => 'hours',
                    'style' => 'width:100px',
            ])->label($process->descrip);
        }

    ?>

    <div class="form-group generated-total">
        <label class="control-label col-sm-4" style="margin-right: 15px" for="total_hours">Total Hours</label>
        <div id="total_hours" class="col-sm-4 flash-success">0.00</div>
    </div>

    <hr>

    <?= $form->field($modelTimesheet, 'remarks')->textarea(['rows' => 3]) ?>

    <?= $form->field($modelTimesheet, 'license_nbr')->widget(Select2::classname(), [
        'size' => Select2::SMALL,
        'initValueText' => $init_contractor,
        'options' => ['placeholder' => 'Search for a contractor...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {search:params.term}; }'),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(license_nbr) { return license_nbr.text; }'),
            'templateSelection' => new JsExpression('function(license_nbr) { return license_nbr.text; }'),
        ],
    ]); ?>

    <?= $form->field($modelTimesheet, "doc_file")->widget(FileInput::className(), [
//        'options' => ['accept' => 'application/pdf'],
        'pluginOptions'=> [
            'allowedFileExtensions'=>['pdf'],
            'showUpload' => false,
            'allowedPreviewTypes' => null,
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

$('.hours').change(function() {
    var sum = 0;
    $('.hours').each(function() {
        sum += Number($(this).val());
    });
    $('#total_hours').html(sum);
});

JS;
$this->registerJs($script);

?>

