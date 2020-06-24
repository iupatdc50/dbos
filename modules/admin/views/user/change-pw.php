<?php

// use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\password\PasswordInput;
?>

<div>
<?php
$form = ActiveForm::begin(['id' => 'change-pw', 'layout' => 'horizontal']);
echo $form->field($model, 'password_current')->passwordInput();
/** @noinspection PhpUnhandledExceptionInspection */
echo $form->field($model, 'password_new')->widget(PasswordInput::className(), [
		'pluginOptions' => [
			'showMeter' => true,
			'toggleMask' => false,	
		],
]);
echo $form->field($model, 'password_confirm')->passwordInput();
/*
echo $form->field($model, 'verify_cd')->widget(Captcha::className(), [
    'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-6">{input}</div></div>',
]);
*/
?>

<div class="form-group">
	<div class="col-lg-offset-2 col-lg-10">
		<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']); ?>
	</div>
</div>

<?php ActiveForm::end(); ?>

</div>


