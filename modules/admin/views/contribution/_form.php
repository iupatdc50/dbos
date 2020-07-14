<?php /** @noinspection PhpUnhandledExceptionInspection */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Contribution */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="fee-type-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'lob_cd')->widget(Select2::className(), [
        'data' => $model->getLobOptions(),
        'hideSearch' => true,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'contrib_type')->widget(Select2::className(), [
        'data' => $model->contribOptions,
        'hideSearch' => true,
        'size' => Select2::SMALL,
        'options' => ['placeholder' => 'Select...'],
    ]) ?>

    <?= $form->field($model, 'wage_pct')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'factor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'operand')->widget(Select2::className(), [
    		'data' => $model->operandOptions,
    		'hideSearch' => true,
			'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    ]) ?>

    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
