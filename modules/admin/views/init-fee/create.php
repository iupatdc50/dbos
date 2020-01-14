<?php /** @noinspection PhpUnhandledExceptionInspection */

use app\helpers\OptionHelper;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\InitFee */

$this->title = 'Create Init Fee';
?>
<div class="init-fee-create">

    <div class="init-fee-form">

        <?php $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'id' => 'init-fee-form',
            'enableClientValidation' => true,
        ]); ?>

        <?= $form->field($model, 'lob_cd')->widget(Select2::className(), [
            'data' => $model->getLobOptions(),
            'size' => Select2::SMALL,
            'options' => ['placeholder' => 'Select trade...'],
        ]) ?>

        <?= $form->field($model, 'member_class')->widget(Select2::className(), [
            'data' => $model->getClassOptions(),
            'size' => Select2::SMALL,
            'options' => ['placeholder' => 'Select member class...'],
        ]) ?>

        <?= $form->field($model, 'effective_dt')->widget(DateControl::className(), [
            'type' => DateControl::FORMAT_DATE,
        ])  ?>

        <?= $form->field($model, 'fee')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'dues_months')->textInput() ?>

        <?= $form->field($model, 'included')->widget(Select2::className(), [
            'data' => OptionHelper::getTFOptions(),
            'hideSearch' => false,
            'size' => Select2::SMALL,
            'options' => ['placeholder' => 'Select...'],
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
