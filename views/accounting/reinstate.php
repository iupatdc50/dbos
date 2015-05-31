<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model app\models\receipt\ReinstateForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="reinstate-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'reinstate-receipt',
    		'enableClientValidation' => true,
    		
    ]); ?>
    
    	<?= $form->field($model, 'receipt_nbr')->textInput();  ?>
	    <?= $form->field($model, 'receipt_dt')->widget(DateControl::className(), [
	    		'type' => DateControl::FORMAT_DATE,
	    ])  ?>
        <?= $form->field($model, 'total_amt')->textInput();  ?>
        <?= $form->field($model, 'reinstate_fee')->textInput();  ?>
        <?= $form->field($model, 'dues')->textInput();  ?>
        <?= $form->field($model, 'other_fees')->textInput();  ?>
        <?= $form->field($model, 'payment_method')->textInput();  ?>
        <?= $form->field($model, 'notes')->textInput();  ?>

            <div class="form-group">
        <?= Html::submitButton('Post', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

        

</div>