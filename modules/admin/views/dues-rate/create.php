<?php

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\DuesRate */

$this->title = 'Create Dues Rate';
?>
<div class="dues-rate-create">

   <div class="dues-rate-form">

        <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'dues-rate-form',
    		'enableClientValidation' => true,
        ]); ?>

        <?= $form->field($model, 'lob_cd')->widget(Select2::className(), [
            'data' => $model->lobOptions,
            'options' => ['placeholder' => 'Select trade...'],
        ]) ?>

        <?= $form->field($model, 'rate_class')->widget(Select2::className(), [
            'data' => $model->rateClassOptions,
            'options' => ['placeholder' => 'Select rate class...'],
        ]) ?>

        <?= $form->field($model, 'effective_dt')->widget(DateControl::className(), [
            'type' => DateControl::FORMAT_DATE,
        ])  ?>

        <?= $form->field($model, 'rate')->textInput(['maxlength' => 7]) ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
