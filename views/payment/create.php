<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model app\models\project\jtp\Payment */

$this->title = 'Create Subsidy Payment';
$this->params['breadcrumbs'][] = ['label' => 'JTP Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="payment-create">

<div class="payment-form">

    <?php $form = ActiveForm::begin([
//    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'payment-form', 
    		'enableClientValidation' => true,
    ]); ?>

	<?= $form->field($model, 'payment_dt')->widget(DateControl::className(), ['type' => DateControl::FORMAT_DATE]) ?>
	<?= $form->field($model, 'paid_amt')->textInput(['maxlength' => true]) ?>
	<?= $form->field($model, 'actual_hrs')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'close_project')->checkbox() ?>
	
    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

    
    
</div>
