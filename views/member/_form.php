<?php

/**
 * Member data entry form partial
 *
 * On create, a single address and single phone may be entered.
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\form\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $modelAddress app\models\member\Address */
/* @var $modelsAddress \yii\db\ActiveQuery */
/* @var $modelPhone app\models\member\Phone */
/* @var $modelsPhone \yii\db\ActiveQuery */
/* @var $modelEmail \yii\db\ActiveQuery */
/* @var $modelsEmail \yii\db\ActiveQuery */
/* @var $modelStatus \yii\db\ActiveQuery */
/* @var $modelsSpecialty \yii\db\ActiveQuery */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="member-form">

    <?php $form = ActiveForm::begin([
    		'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
//    		'type' => ActiveForm::TYPE_HORIZONTAL
    		
    ]); ?>
    
    <div class="leftside fortyfive-pct">

    <?php if ($model->isNewRecord): ?>
    
    <?= $form->field($model, 'photo_file')->widget(FileInput::className(), [
    		'options' => ['accept' => 'image/*'],
    		'pluginOptions' => ['showUpload' => false],
    ]);    ?>
    
    <?php else: ?>
    <?php $title = isset($model->photo_id) && !empty($model->photo_id) ? $model->photo_id : 'Avatar'; ?>
    <?= Html::img($model->imageUrl, [
			'class' =>'img-thumbnail',
			'width' => '75',
			'height' => '100',
			'alt' => $title,
			'title' => $title,
    ]) ?>
    
	<?php endif ?>
    
    <?= $form->field($model, 'last_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'first_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'middle_inits')->textInput(['maxlength' => 7]) ?>

    <?= $form->field($model, 'suffix')->textInput(['maxlength' => 7]) ?>

    <?= $form->field($model, 'birth_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <?= $form->field($model, 'gender')->widget(Select2::className(), [
    		'data' => OptionHelper::getGenderOptions(), 
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

	<?= $form->field($model, 'ssnumber')->textInput(['maxlength' => 11]) ?>

    <?= $form->field($model, 'imse_id')->textInput(['maxlength' => 20]) ?>
    
    <?= $form->field($model, 'shirt_size')->widget(Select2::className(), [
    		'data' => $model->sizeOptions,
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'local_pac')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...', 'id' => 'localpac'],
    ]) ?>

    <?= $form->field($model, 'ncfs_id')
        	 ->textInput(['maxlength' => true, 'id' => 'ncfsid'])
    		 ->label('NCFS ID', ['id' => 'ncfslbl']) ?>

    <?= $form->field($model, 'hq_pac')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => false,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?php if(Yii::$app->user->can('resetPT')): ?>
    <?= $form->field($model, 'overage')->textInput(['maxlength' => true]) ?>
    <?php endif; ?>

    <hr>
	    <div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
    
    </div>
	<div class="rightside fifty-pct">
    <?php if ($model->isNewRecord): ?>
    	
    	<?= $this->render('_insertformfields', [
    			'form' => $form,
    			'model' => $model,
    			'modelAddress' => $modelAddress,
    			'modelPhone' => $modelPhone,
    			'modelEmail' => $modelEmail,
    			'modelStatus' => $modelStatus,
    			'modelClass' => $modelClass,
    	]) ?>
    	
    	
    <?php else: ?>
    
    	<?= $this->render('_updategrids', [
    			'form' => $form,
    			'model' => $model,
    			'modelsAddress' => $modelsAddress,
    			'modelsPhone' => $modelsPhone,
    			'modelsEmail' => $modelsEmail,
    			'modelsSpecialty' => $modelsSpecialty,
    	]) ?>
	<?php endif ?>

	<?php ActiveForm::end(); ?>
	
	
	</div>
	
	    
</div>

<?php 
$script = <<< JS

$(function() {
	toggle($('#localpac').val());
})

$('#localpac').change(function() {
	toggle($(this).val());
});
 		
function toggle(pac) {
	if(pac == 'T') {
		$('#ncfslbl').show();
		$('#ncfsid').show();
	} else {
		$('#ncfslbl').hide();
		$('#ncfsid').hide();
	};
}

JS;
$this->registerJs($script);
?>