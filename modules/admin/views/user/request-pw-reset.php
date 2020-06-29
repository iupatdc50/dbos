<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>


<div style="width: 50%">
    <h3>Request Password Reset</h3>
    <div style="color:#999;">A temporary password will be sent to the email below</div>
    <br />
<?php
$form = ActiveForm::begin(['id' => 'request-pw-reset-form']);
/* @var $form yii\bootstrap\ActiveForm */
echo $form->field($model, 'email');
echo Html::submitButton(
		'Submit',
		['class' => 'btn btn-primary', 'name' => 'reset-button']
);
ActiveForm::end();
?>
</div>
