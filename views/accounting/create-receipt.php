<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\Receipt */

$this->title = 'Choose Receipt Type';

?>

<div class="recptchoice-create">

<div class="recptchoice-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'recptchoice-form', 
    		'enableClientValidation' => true,
    ]); ?>
    
    <?= $form->field($model, 'lob_cd')->widget(Select2::className(), [
    		'data' => $model->lobOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Local...'],
    ]) ?>

    <?= $form->field($model, 'payor_type')->radioList($model->PayorOptions)->label('Select'); ?>

    <div class="form-group">
        <?= Html::submitButton('Begin', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

    
    
</div>
    