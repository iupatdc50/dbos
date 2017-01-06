<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;


/* @var $this yii\web\View */
/* @var $model app\models\member\MemberClass */

?>
<div class="member-class-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
    		'id' => 'member-class-form',
    		'enableClientValidation' => true,
    		
    ]); ?>

    <?= $form->field($model, 'effective_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
    
    	<?= $this->render('_formfields', [
    			'form' => $form,
    			'modelClass' => $model,
    	]) ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>


</div>
