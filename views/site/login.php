<?php

use himiklab\yii2\recaptcha\ReCaptcha2;
use yii\helpers\Html;
// use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
?>


<div style="width: 50%">
<?php
$form = ActiveForm::begin(['id' => 'login-form']);
/* @var $form yii\bootstrap\ActiveForm */
echo $form->field($model, 'username');
echo $form->field($model, 'password')->passwordInput();
echo $form->field($model, 'rememberMe')->checkbox();

if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443) {
    /** @noinspection PhpUnhandledExceptionInspection */
    echo $form->field($model, 'reCaptcha')->widget(ReCaptcha2::className())->label(false);
}

echo Html::submitButton(
		'Login',
		['class' => 'btn btn-primary', 'name' => 'login-button']
);
ActiveForm::end();
?>
    <br />
    <div style="color:#999;">
        <p>Forgot Password? <?= Html::a('Click here', '/admin/user/request-pw-reset'); ?></p>
    </div>


</div>
