<?php

use app\models\member\ReinstateForm;
use kartik\widgets\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\member\ReinstateForm */

// The controller action that will render the list
$url = Url::to(['/admin/user/user-list']);

?>

<div class="reinstate-form">

	<?php $form = ActiveForm::begin([
    		'id' => 'reinstate-form',
    		'enableClientValidation' => true,
			'enableAjaxValidation' => true,

    ]); ?>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    $form->field($model, 'type')->widget(Select2::className(), [
        'data' => $model->getTypeOptions(),
        'hideSearch' => false,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Select...', 'id' => 'type'],
    ]) ?>

    <div id="dues">
        <?= $form->field($model, 'assessments_b')->checkboxList($model->getAssessmentOptions(ReinstateForm::TYPE_BACKDUES), [
            'multiple' => true,
            'id' => 'duescbl',
            'itemOptions' =>  ['disabled' => true],
        ]) ?>
    </div>

    <div id="apf">
        <?= $form->field($model, 'assessments_a')->checkboxList($model->getAssessmentOptions(ReinstateForm::TYPE_APF), [
            'multiple' => true,
            'id' => 'apfcbl',
        ]) ?>
    </div>

    <div id="waive">
        <?= /** @noinspection PhpUnhandledExceptionInspection */
        $form->field($model, 'authority')->label('Authorized by')->widget(Select2::classname(), [
            'size' => Select2::SMALL,
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    // only select BRs (role=30)
                    'data' => new JsExpression('function(params) { return {search:params.term,role:"30"}; }'),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(id) { return id.text; }'),
                'templateSelection' => new JsExpression('function(id) { return id.text; }'),
            ],
        ]) ?>

    </div>

    <hr>

    <div class="form-group">
        <?= Html::submitButton('Reinstate', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(function() {
    $('#dues').hide();
    $('#apf').hide();
});

$('#type').change(function() {
    toggleOptions($(this).val());
});

$('#duescbl').on('click', ':checkbox', function() {
    toggleWaive($('#dues input[type="checkbox"]'));
});

$('#apfcbl').on('click', ':checkbox', function() {
    toggleWaive($('#apf input[type="checkbox"]'));
});

function toggleOptions(atype) {
    const apf = $('#apf');
    const dues = $('#dues');
    const waive = $('#waive');
    dues.hide();
    apf.hide();
    waive.hide();
    switch (atype) {
        case 'B':
            dues.show();
            toggleWaive($('#dues input[type="checkbox"]'));
            break; 
        case 'A':
            apf.show();
            toggleWaive($('#apf input[type="checkbox"]'));
            break; 
        case 'W':
            waive.show();
            break;
    }
}

function toggleWaive(alist) {
    const waive = $('#waive');
    const tot=alist.length;
    const sel=alist.filter(':checked').length
    if (sel === tot )
        waive.hide();
    else
        waive.show();
}

JS;
$this->registerJs($script);
?>

