<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\checkbox\CheckboxX;


/* @var $this yii\web\View */
/* @var $model app\models\HomeEvent */

?>

<div class="home-event-create">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'home-event-form',
    		'enableClientValidation' => true,
//    		'enableAjaxValidation' => true,
    		    		
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'all_day_ckbox')->widget(CheckboxX::className(), [
    		'pluginOptions' => [
    				'threeState' => false,
    		],
    		'pluginEvents' => [
    				'change' => "function () {
    					if ($(this).val() == '0') {
    						\$('#duration-only').show();
    						\$('#all-day').hide();
						} else {
    						\$('#duration-only').hide();
    						\$('#all-day').show();
 						}
					}",
    		],
    ]); ?>

    <?= $form->field($model, 'start_dt_part')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ]) ?>
    
    <div id="duration-only">

    <?= $form->field($model, 'start_tm_part')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_TIME,
    ]) ?>
    
    <?= $form->field($model, 'duration')->textInput(['maxlength' => true]); ?>
   
   </div>
   <div id="all-day">

    <?= $form->field($model, 'end_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ]) ?>
    
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(function() {
		$('#duration-only').show();
		$('#all-day').hide();
})
		
JS;
$this->registerJs($script);
?>
