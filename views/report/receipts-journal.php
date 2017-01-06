<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\report\BaseSettingsForm */

?>

<h1>Cash Receipts Journal Settings</h1>

<div class="receipts-journal">

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
       		'id' => 'contractor-info', 
    ]); ?>

    <?= $this->render('_dateformfields', [
        'model' => $model,
    	'form' => $form,
    ]) ?>

    <?= $this->render('_baseformfields', [
        'model' => $model,
    	'form' => $form,
    ]) ?>

    <?php ActiveForm::end(); ?>
    
</div>
