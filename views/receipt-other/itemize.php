<?php

use yii\helpers\Html;


    /* @var $searchAlloc app\models\accounting\StagedAllocationSearch */
    /* @var $allocProvider yii\data\ActiveDataProvider */
    /* @var $modelReceipt app\models\accounting\ReceiptContractor */
    /* @var $this \yii\web\View */


    $this->title = 'Build Receipt (Other) ' . $modelReceipt->id;
    $this->params['breadcrumbs'][] = ['label' => 'Other Receipts', 'url' => ['index']];
    $this->params['breadcrumbs'][] = $modelReceipt->id;


    ?>
    <div class="receipt-view">

        <h1><?= Html::encode($this->title) ?></h1>

        <div id="stagetoolbar">
            <?= $this->render('../receipt/_updatetoolbar', ['modelReceipt' => $modelReceipt]); ?>
        </div>
        <?= $this->render('../receipt/_detail', ['modelReceipt' => $modelReceipt]); ?>

        <?= $this->render('../receipt/_itemizegridmulti', [
            'allocProvider' => $allocProvider,
            'modelReceipt' => $modelReceipt,
            'searchAlloc' => $searchAlloc,

        ]); ?>


