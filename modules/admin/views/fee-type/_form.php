<?php /** @noinspection PhpUnhandledExceptionInspection */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\helpers\OptionHelper;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\FeeType */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="fee-type-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'fee_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descrip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'short_descrip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'freq')->dropDownList([ 'R' => 'R', 'M' => 'M', 'O' => 'O', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'is_assess')->widget(Select2::className(), [
    		'data' => OptionHelper::getTFOptions(), 
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'contribution')->widget(Select2::className(), [
        'data' => OptionHelper::getTFOptions(),
        'hideSearch' => true,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Select...'],
    ]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
