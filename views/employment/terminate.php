<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\datecontrol\DateControl;


/* @var $this yii\web\View */
/* @var $model app\models\member\Employment */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="employment-create">

<div class="employment-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
    		'id' => 'employ-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'end_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <?= $form->field($model, 'term_reason', ['options' => ['id' => 'termreason']])->radioList($termReasonOptions)->label('Reason'); ?>
    
    <div class="form-group">
        <?= Html::submitButton('Terminate', ['class' => 'btn btn-danger']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
    
</div>
