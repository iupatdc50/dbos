<?php

use app\models\training\ArchiveTimesheetForm;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $archiveForm  ArchiveTimesheetForm */
/* @var $this View */

?>

<div class="archive-form">

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'id' => 'archive-create',
    		'enableClientValidation' => true,
    		'options' => ['enctype' => 'multipart/form-data'],
    ]);

    ?>

    <div class="form-group">
        <label class="control-label col-sm-3" for="membid">Member</label>
        <div class="col-sm-6">
            <input type="text" id="membid" class="form-control" readonly="" aria-required="true" aria-invalid="false" value="<?= $archiveForm->member_nm ?>">
        </div>
    </div>

    <?= $form->field($archiveForm, 'member_id')->hiddenInput()->label(false) ?>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    $form->field($archiveForm, 'lob_cd')->widget(Select2::className(), [
        'initValueText' => $archiveForm->lob_descrip,
        'data' => $archiveForm->getLobOptions(),
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Select Local...', 'id' => 'lob'],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Recover', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

