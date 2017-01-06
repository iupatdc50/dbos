<?php

use yii\helpers\Html;
use kartik\widgets\Select2;

?>

<div class="base-fields">

    <?= $form->field($model, 'lob_cd')->widget(Select2::className(), [
    		'data' => $model->lobOptions, 
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'All'],
    		'pluginOptions' => ['allowClear' => true],
    ]) ?>

    <?php if ($model->show_islands) : ?>
    <?= $form->field($model, 'island')->widget(Select2::className(), [
    		'data' => $model->islandOptions, 
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'All'],
    		'pluginOptions' => ['allowClear' => true],
    ]) ?>
    <?php endif; ?>
    
    
    <?= $form->field($model, 'output_to')->widget(Select2::className(), [
    		'data' => $model->outputToOptions, 
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select Destination...'],    		
    ]) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Generate Report', ['class' => 'btn btn-primary']) ?>
    </div>
    
    

</div>

