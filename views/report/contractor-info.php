<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\report\BaseSettingsForm */

?>

<h1>Contractor Information Report Settings</h1>

<div class="contractor-info">

    <?php $form = ActiveForm::begin([
//    		'layout' => 'horizontal',
       		'id' => 'contractor-info', 
    ]); ?>

    <?= $this->render('_baseformfields', [
        'model' => $model,
    	'form' => $form,
    ]) ?>

    <?php ActiveForm::end(); ?>
    
</div>
