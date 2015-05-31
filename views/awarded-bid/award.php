<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;


/* @var $this yii\web\View */
/* @var $model app\models\project\AwardedBid */

?>
<div class="award-create">

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
    		'id' => 'award-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <?= Html::activeHiddenInput($model, 'registration_id');  ?>
    
    <?= $form->field($model, 'start_dt')->widget(DateControl::className(), [
    		'type' => DateControl::FORMAT_DATE,
    ])  ?>
<br />
    <div class="form-group">
        <?= Html::submitButton('Process Award', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
</div>
