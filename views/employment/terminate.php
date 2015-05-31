<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;


/* @var $this yii\web\View */
/* @var $model app\models\member\Employment */
/* @var $form yii\widgets\ActiveForm */

?>
<div class="employment-create">

<div class="employment-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'employ-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'end_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
    
    <div class="form-group">
        <?= Html::submitButton('Terminate', ['class' => 'btn btn-danger']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
</div>
