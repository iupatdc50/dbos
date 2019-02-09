<?php

use app\models\accounting\AllocatedMemberSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $modelReceipt app\models\accounting\Receipt */
/* @var $membProvider ActiveDataProvider */
/* @var $searchMemb AllocatedMemberSearch */

$this->title = 'Update Receipt: ' . ' ' . $modelReceipt->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $modelReceipt->id, 'url' => ['view', 'id' => $modelReceipt->id]];
$this->params['breadcrumbs'][] = 'Update';

?>
<div class="receipt-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $form = ActiveForm::begin([
        'enableClientValidation' => true,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $this->render('../receipt/_updatetoolbar', [
        'modelReceipt' => $modelReceipt,
    ]) ?>

    <div class="leftside forty-pct">


    <?= $form->field($modelReceipt, 'payor_nm')->textInput(['maxlength' => true]); ?>

    <?= $this->render('../receipt/_formfields', [
        'form' => $form,
        'model' => $modelReceipt,
        'opt' => '',
    ]) ?>

    <?= $form->field($modelReceipt, 'unallocated_amt')->textInput(['maxlength' => true, 'readonly' => (!$modelReceipt->isNewRecord)]) ?>


    <?php ActiveForm::end(); ?>

    </div>

    <div class="rightside fiftyfive-pct">

        <?= $this->render('../receipt/_updategridmulti',[
            'searchMemb' => $searchMemb,
            'modelReceipt' => $modelReceipt,
            'membProvider' => $membProvider,

        ]); ?>

    </div>

</div>

<?= $this->render('../partials/_modal') ?>

