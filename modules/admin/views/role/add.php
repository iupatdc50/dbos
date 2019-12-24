<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\user\AssignmentForm */

?>

<div class="addrole-form">

<?php

$form = ActiveForm::begin([
    'id' => 'addrole-form',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'enableClientValidation' => true,
]);

/* @var $form yii\bootstrap\ActiveForm */

?>

<?= $form->field($model, 'staff_roles_only')->checkbox(['id' => 'staff-only']); ?>

<div id="staff-role">
<?= /** @noinspection PhpUnhandledExceptionInspection */
$form->field($model, 'staff_role')->widget(Select2::className(), [
    'data' => $model->user->getRoleOptions(),
    'size' => Select2::SMALL,
    'options' => ['placeholder' => 'Select Staff Role...'],
])->label('Role'); ?>
</div>

<div id="action-roles">
<?= /** @noinspection PhpUnhandledExceptionInspection */
$form->field($model, 'action_roles')->listBox($model->user->getRoleOptions(false), [
    'multiple' => true,
    'size' => 10,
])->label('Role'); ?>
</div>

<?= Html::submitButton(
    'Assign',
    ['class' => 'btn btn-success', 'name' => 'assign-button']
); ?>

<?php
ActiveForm::end();

?>

</div>

<?php
$script = <<< JS

$(function() {
    toggle($('#staff-only'));
});

$('#staff-only').change(function() {
    toggle($(this));
});

function toggle(staff) {
    if(staff.is(":checked")) {
        $('#staff-role').show();
        $('#action-roles').hide();
    } else {
        $('#staff-role').hide();
        $('#action-roles').show();
    }
}

JS;
$this->registerJs($script);
?>

