<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $modelReceipt app\models\accounting\Receipt */
/* @var $controller */

$this->title = 'Update Receipt: ' . ' ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $modelReceipt->id, 'url' => ['view', 'id' => $modelReceipt->id]];
$this->params['breadcrumbs'][] = 'Update';

?>
<div class="receipt-update">

    <div id="running-display" class="pull-right">
        <div id="running-total" class="flash-success"></div>
    </div>
    <div id="balance-display" class="pull-right">
        <div id="out-of-balance" class="flash-error"></div>
    </div>

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'enableClientValidation' => true,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $this->render('_formfields', [
        'form' => $form,
        'model' => $modelReceipt,
    ]) ?>

    <?php if ($controller == 'receipt-contractor'): ?>

    <?= $form->field($modelReceipt, 'unallocated_amt')->textInput(['maxlength' => true]) ?>

    <?= $this->render('_helperfields', [
        'form' => $form,
        'model' => $modelReceipt,
    ]) ?>

    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(function() {
		refreshToolBar($modelReceipt->id);
})
		
function refreshToolBar(id) {
	$.get('/$controller/balances-json', { id : id }, function(data) {
		// noinspection JSUndeclaredVariable
        result = $.parseJSON(data);
        if (result.balance == 0.00) {
			$('#balance-display').hide();
		} else {
			$('#out-of-balance').html("Out of Balance: " + result.balance);
			$('#balance-display').show();
		}
		$('#running-total').html("Total Allocation: " + result.running);
	});
}

JS;

$pos = \yii\web\View::POS_READY;
$this->registerJs($script, $pos);
?>