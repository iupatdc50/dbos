<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;


/* @var $this yii\web\View */
/* @var $model app\models\report\ExportCsvForm */

?>

<h1>Export PAC Settings</h1>

<div class="export-pac-settings col-sm-6">

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
       		'id' => 'settings-info', 
    ]); ?>

    <?= $this->render('_dateformfields', [
        'model' => $model,
    	'form' => $form,
    ]) ?>

    <?= $this->render('_baseformfields', [
        'model' => $model,
    	'form' => $form,
    ]) ?>
    
    <?= $form->field($model, 'enclosure')->widget(Select2::className(), [
    		'data' => $model->encloseOptions,
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    ]) ?>
 
    <?= $form->field($model, 'delimiter')->widget(Select2::className(), [
    		'data' => $model->cellOptions,
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    ]) ?>
 
    <div class="form-group">
        <?= Html::submitButton('Export', ['class' => 'btn btn-primary']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
    
</div>
