<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\ResponsibleEmployer */

?>

<div class="respemployer-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'options' => ['class' => 'ajax-create'], // Required for modal within an update
        'id' => 'respemployer-form',
        'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'license_nbr')->widget(Select2::classname(), [
        'size' => Select2::SMALL,
        'initValueText' => $model->employer->contractor,
        'options' => ['id' => 'license-nbr', 'placeholder' => 'Search for a contractor...'],
        'pluginOptions' => [
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => Url::to(['/contractor/contractor-list']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {search:params.term}; }'),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(employer) { return employer.text; }'),
            'templateSelection' => new JsExpression('function(employer) { return employer.text; }'),
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Reassign', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

