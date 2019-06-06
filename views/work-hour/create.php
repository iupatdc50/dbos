<?php

use app\models\training\Timesheet;
use app\models\training\WorkHour;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $modelHour WorkHour */
/* @var $modelTimesheet Timesheet */

?>

<div class="hour-form">

    <?php $form = ActiveForm::begin([
  		'type' => ActiveForm::TYPE_HORIZONTAL,
        'options' => ['class' => 'ajax-create'], // Required for modal within an update
        'id' => 'payment-form',
        'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($modelHour, 'wp_seq')->widget(Select2::className(), [
        'size' => Select2::SMALL,
        'data' => $modelTimesheet->member->procOptions,
        'options' => ['placeholder' => 'Select month...'],
    ]) ?>
    <?= $form->field($modelHour, 'hours')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
