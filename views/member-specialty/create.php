<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;
use kartik\depdrop\DepDrop;

/* @var $this yii\web\View */
/* @var $model app\models\member\Specialty */
/* @var $form kartik\form\ActiveForm */
?>

<div class="specialty-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
       		'id' => 'address-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'trade')->widget(Select2::className(), [
    		'data' => $model->tradeOptions, 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    		
    ]) ?>

    
    <?= $form->field($model, 'specialty')->widget(DepDrop::className(), [
    		'type' => DepDrop::TYPE_SELECT2,
       		'select2Options' => [
    				'size' => Select2::SMALL,
    				'hideSearch' => true,
   			 ],
    		'pluginOptions' => [
    				'depends' => ['specialty-trade'], 
    				'placeholder' => 'Select...',
    				'url' => Url::to(['/member-specialty/specialty']),
    		],
    		
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>