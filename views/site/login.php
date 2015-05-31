<?php
use yii\helpers\Html;
// use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveForm;
?>


<div style="width: 50%">
<?php
$form = ActiveForm::begin(['id' => 'login-form', 'layout' => 'horizontal']);
/* @var $form yii\bootstrap\ActiveForm */
echo $form->field($model, 'username');
echo $form->field($model, 'password')->passwordInput();
echo $form->field($model, 'rememberMe')->checkbox();
echo Html::submitButton(
		'Login',
		['class' => 'btn margin-lft-25 btn-primary', 'name' => 'login-button']
);
ActiveForm::end();
?>

</div>
