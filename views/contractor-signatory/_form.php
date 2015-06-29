<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Signatory */
/* @var $form kartik\form\ActiveForm */
?>

<div class="signatory-form">

    <?php $form = ActiveForm::begin([
    		'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
    		'type' => $model->isNewRecord ? ActiveForm::TYPE_VERTICAL : ActiveForm::TYPE_HORIZONTAL,
    		'id' => 'signatory-form',
    		'enableClientValidation' => true,
    ]); ?>
    
    <?= $this->render('_formfields', ['modelSig' => $model, 'form' => $form]); ?>    

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
