<?php

/**
 * Contractor data entry form partial
 *
 * On create, a single address and single phone may be entered.
 */

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $modelSig app\models\contractor\Signatory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contractor-form">

    <div class="leftside fortyfive-pct">
    
    <?= $form->field($model, 'license_nbr')->textInput(['maxlength' => 8]) ?>

    <?= $form->field($model, 'contractor')->textInput(['maxlength' => 60]) ?>

    <?= $form->field($model, 'contact_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => 100]) ?>
    
    <?= $form->field($model, 'deducts_dues')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'is_active')->widget(Select2::className(), [
    		'data' => $model->statusOptions, 
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Status...'],
    ]) ?>
    
    <?php if ($model->isNewRecord): ?>
    	<?= $this->render('../contractor-signatory/_formfields', ['modelSig' => $modelSig, 'form' => $form]); ?>
	<?php endif ?>
    
    <hr>
	    <div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
        
    </div>
    
</div>




