<?php

use app\models\accounting\AllocatedMemberSearch;
use app\models\accounting\Receipt;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
// use yii\bootstrap\ActiveForm;
use kartik\form\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $modelReceipt app\models\accounting\Receipt */
/* @var $allocProvider ActiveDataProvider */
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
//        'type' =>  ActiveForm::TYPE_HORIZONTAL,
        'enableClientValidation' => true,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $this->render('../receipt/_updatetoolbar', [
        'modelReceipt' => $modelReceipt,
    ]) ?>

    <div class="leftside forty-pct">

    <?= $this->render('../receipt/_formfields', [
        'form' => $form,
        'model' => $modelReceipt,
    ]) ?>

    <?php ActiveForm::end(); ?>

    </div>

    <div class="rightside fiftyfive-pct">

        <?= $this->render('../receipt/_allocgrid', [
                'allocProvider' => $allocProvider,
                'alloc_memb_id' => $modelReceipt->allocatedMembers[0]->id,
        ])
        ?>
    </div>


</div>

<?= $this->render('../partials/_modal') ?>

