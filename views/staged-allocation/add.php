<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\StagedAllocation */
/* @var $license_nbr string */

// The controller action that will render the list
$url = Url::to(['/member/member-ssn-list']);

$member_name = isset($model->member) ? $model->member->fullName : 'Search for employee...';
$tabindex = 0;
?>

<div class="allocation-add">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => 'allocation-add',
        'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'member_id')->label('Emloyee')->widget(Select2::classname(), [
        'size' => Select2::SMALL,
        'initValueText' => $member_name,
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {search:params.term,employer:"'. $license_nbr .  '"}; }'),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(member_id) { return member_id.text; }'),
            'templateSelection' => new JsExpression('function(member_id) { return member_id.text; }'),
        ],
    ]) ?>

    <?php
        foreach ($model->feeLabels as $field=>$label) {
            if (!in_array($field, ['receipt_id', 'alloc_memb_id', 'member_id']))
                echo $form->field($model, $field)->textInput(['maxlength' => true, 'style' => 'width:100px'])->label($label);
        }
    ?>

    <div class="form-group">
        <?= Html::submitButton('Add', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('close', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$script = <<< JS

// Treat [enter] as [tab]
$(document).keydown(function(e) {

  // Set self as the current item in focus
  var self = $(':focus'),
      // Set the form by the current item in focus
      form = self.parents('form:eq(0)'),
      focusable;

  // Array of Indexable/Tab-able items
  focusable = $('div.allocation-add').find('input,a,select,button,textarea').filter(':visible');

  function enterKey(){
    if (e.which === 13 && !self.is('textarea')) { // [Enter] key

      // If not a regular hyperlink/button/textarea
      if ($.inArray(self, focusable) && (!self.is('a')) && (!self.is('button'))){
        // Then prevent the default [Enter] key behaviour from submitting the form
        e.preventDefault();
      } // Otherwise follow the link/button as by design, or put new line in textarea

      // Focus on the next item (either previous or next depending on shift)
      focusable.eq(focusable.index(self) + (e.shiftKey ? -1 : 1)).focus();

      return false;
    }
  }
  // We need to capture the [Shift] key and check the [Enter] key either way.
  if (e.shiftKey) { enterKey() } else { enterKey() }
});

var select2_open;
// open select2 dropdown on focus
$(document).on('focus', '.select2-selection--single', function(e) {
    select2_open = $(this).parent().parent().siblings('select');
    select2_open.select2('open');
});

JS;
$this->registerJs($script);

?>