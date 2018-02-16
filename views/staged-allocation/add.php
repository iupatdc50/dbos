<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\StagedAllocation */
/* @var $lob_cd string */

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

    <?= $form->field($model, 'member_id')->label('Employee')->widget(Select2::classname(), [
        'size' => Select2::SMALL,
        'initValueText' => $member_name,
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {search:params.term,lob_cd:"'. $lob_cd .  '"}; }'),
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
        <?= Html::submitButton('Add', ['id' => 'addbtn', 'class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php
    foreach (Yii::$app->session->getAllFlashes() as $key => $messages) {
        $message = (is_array($messages)) ? implode(', ', $messages) : $messages;
        echo '<div class="flash-' . $key . '">' . $message . '</div>';
    } ?>
</div>

<?php

$script = <<< JS

$('#modalCreate').on('shown.bs.modal', function() {    
    $('#stagedallocation-member_id').siblings('span').children('.selection').children('.select2-selection--single').trigger('focus');
    select2_open = $(this).parent().parent().siblings('select');
    select2_open.select2('open');
});

$(document).keydown(function(e) {

  // Set self as the current item in focus
  var self = $(':focus'),
      form = self.parents('form:eq(0)'),
      next,
      focusable;

  focusable = $('div.allocation-add').find('input,a,select,button,textarea').filter(':visible');

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

var select2_open;
// open select2 dropdown on focus
$(document).on('focus', '.select2-selection--single', function(e) {
    select2_open = $(this).parent().parent().siblings('select');
    select2_open.select2('open');
});

JS;
$this->registerJs($script);

?>