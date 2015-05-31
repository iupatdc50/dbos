<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;


/* @var $this yii\web\View */
/* @var $model app\models\project\CancelForm */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="cancel-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'cancel-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'cancel_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
    
    <?= $form->field($model, 'reason')->textarea(['rows' => 6]) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Cancel Project', ['class' => 'btn btn-danger']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
</div>
