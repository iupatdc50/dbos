<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\ReceiptOther */

$this->title = 'Create Receipt (Other)';
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="receipt-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
    		'layout' => 'horizontal',
    		'enableClientValidation' => true,
    		'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>
    
    <?= $this->render('../receipt/_formfields', [
    	'form' => $form,
        'model' => $model,
        'opt' => '(Optional)',
    ]) ?>
    
    <?= $form->field($model, 'unallocated_amt')->textInput(['maxlength' => true]) ?>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    $form->field($model, 'fee_types')->checkboxList($model->getFeeOptions(), [
    		'multiple' => true,
    ]) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>


