<?php

use app\models\training\ArchiveTimesheetForm;
use kartik\checkbox\CheckboxX;
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

    <div id="mhcheckbox">
        <?= /** @noinspection PhpUnhandledExceptionInspection */
        $form->field($archiveForm, 'is_mh')->widget(CheckboxX::className(), ['pluginOptions' => ['threeState' => false]]); ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Archive', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(function() {
	toggle($('#lob').val());
});

$('#lob').change(function() {
	toggle($(this).val());
});
 		
function toggle(lob) {
	if(lob == '1926') {
		$('#mhcheckbox').show();
	} else {
		$('#mhcheckbox').hide();
	}
}

JS;
$this->registerJs($script);
