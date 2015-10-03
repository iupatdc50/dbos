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
    
    <?= $form->field($model, 'application_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ]) ?>

	<?= $form->field($model, 'ssnumber')->textInput(['maxlength' => 11]) ?>

    <?= $form->field($model, 'imse_id')->textInput(['maxlength' => 20]) ?>
    
    <?= $form->field($model, 'last_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'first_nm')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'middle_inits')->textInput(['maxlength' => 7]) ?>

    <?= $form->field($model, 'suffix')->textInput(['maxlength' => 7]) ?>

    <?= $form->field($model, 'birth_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>

    <?= $form->field($model, 'gender')->widget(Select2::className(), [
    		'data' => OptionHelper::getGenderOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'shirt_size')->widget(Select2::className(), [
    		'data' => $model->sizeOptions,
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'local_pac')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'hq_pac')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <hr>
	    <div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
    
    </div>

    <?php if ($model->isNewRecord): ?>
    	<div class="rightside fifty-pct">
    	<hr>
    	<?= $this->render('../partials/_addressformfields',
    			[
    				'form'	=> $form,
    				'address' => $modelAddress,
    			]
    	) ?>
    	<hr>
    	<?= $this->render('../partials/_phoneformfields',
    			[
    				'form'	=> $form,
    				'phone' => $modelPhone,
    			]
    	) ?>
    	<hr>
    	<?= $form->field($modelEmail, 'email')->textInput(['maxlength' => 50]) ?>
    	<?= $form->field($modelStatus, 'lob_cd')->textInput(['maxlength' => 4]) ?>
    	</div>
	<?php endif ?>

    <?php ActiveForm::end(); ?>

</div>

    <?php if (!$model->isNewRecord): ?>
<hr>
    	<div class="rightside fifty-pct">
		<?= $this->render(
	    		'../partials/_addressgrid',
	    		[
	    			'modelsAddress' => $modelsAddress,
	    			'controller' => 'member-address',
	    			'relation_id' => $model->member_id,	
	    		]
	    ) ?>
        <?= $this->render(
	    		'../partials/_phonegrid',
	    		[
	    			'modelsPhone' => $modelsPhone,
	    			'controller' => 'member-phone',
	    			'relation_id' => $model->member_id,	
	    		]
	    ) ?>
	    <?= $this->render(
	    		'../member-email/_grid',
	    		[
	    			'modelsEmail' => $modelsEmail,	
	    			'relation_id' => $model->member_id,
	    		]
	    ) ?>
        <?= $this->render(
	    		'../member-specialty/_grid',
	    		[
	    			'modelsSpecialty' => $modelsSpecialty,	
	    			'relation_id' => $model->member_id,
	    		]
	    ) ?>
	    </div>
	<?php endif ?>
    
    <?= $this->render('../partials/_modal') ?>
	    
