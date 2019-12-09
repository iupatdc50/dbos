<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\report\CredentialForm */

// The controller action that will render the list
$url = Url::to(['/member/member-ssn-list']);

?>

<h1>Training History</h1>

<div class="training-history col-sm-6">

    <?php $form = ActiveForm::begin([
//            'layout' => 'horizontal',
            'id' => 'settings-info',
    ]); ?>

    <?= $form->field($model, 'option')->widget(Select2::className(), [
        'data' => $model->optionOptions,
        'hideSearch' => true,
        'size' => Select2::SMALL,
    ]) ?>

    <?= $form->field($model, 'member_id')->label('Member')->widget(Select2::classname(), [
        'size' => Select2::SMALL,
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {search:params.term}; }'),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(member_id) { return member_id.text; }'),
            'templateSelection' => new JsExpression('function(member_id) { return member_id.text; }'),
        ],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Export to Excel', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
