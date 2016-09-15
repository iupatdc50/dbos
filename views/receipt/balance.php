<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\StagedAllocation */
/* @var $receipt_id string */

?>

<div class="receipt-balance">
<div class="balance-form"> 

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'balance-form', 
    		'enableClientValidation' => true,
    ]); ?>
    
    <?=  $form->field($model, 'unallocated_amt') ?>

    <div class="form-group">
        <?= Html::submitButton('Balance', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    

</div>

</div>