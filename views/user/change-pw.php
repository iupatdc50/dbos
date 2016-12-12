<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\password\PasswordInput;
?>


<?php
$form = ActiveForm::begin(['id' => 'change-pw', 'layout' => 'horizontal']);
echo $form->field($model, 'password_current')->passwordInput();
echo $form->field($model, 'password_new')->widget(PasswordInput::className(), [
		'pluginOptions' => [
			'showMeter' => true,
			'toggleMask' => false,	
		],
]);
echo $form->field($model, 'password_confirm')->passwordInput();
?>

<div class="form-group">
	<div class="col-lg-offset-2 col-lg-10">
		<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']); ?>
	</div>
</div>
<?php ActiveForm::end(); ?>

