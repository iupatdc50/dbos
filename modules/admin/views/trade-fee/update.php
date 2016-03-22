<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\TradeFee */

$this->title = 'Update Trade Fee: ' . ' ' . $model->lob_cd;
$this->params['breadcrumbs'][] = ['label' => 'Trade Fees', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->lob_cd, 'url' => ['view', 'lob_cd' => $model->lob_cd, 'fee_type' => $model->fee_type]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="trade-fee-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
