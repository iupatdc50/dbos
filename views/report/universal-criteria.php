<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\report\UniversalFileForm */


?>

<h1>Universal File</h1>

<div class="universal-file col-sm-6">

    <?php $form = ActiveForm::begin([
            'id' => 'settings-info',
    ]); ?>

    <?= $form->field($model, 'acct_month')->textInput(['maxlength' => 6])->input('string', ['placeholder' => 'YYYYMM']) ?>

    <div class="form-group">
        <?= Html::submitButton('Export to Excel', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
