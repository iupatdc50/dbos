<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\TradeFee */

$this->title = $model->lob_cd;
$this->params['breadcrumbs'][] = ['label' => 'Trade Fees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trade-fee-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'lob_cd' => $model->lob_cd, 'fee_type' => $model->fee_type], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'lob_cd' => $model->lob_cd, 'fee_type' => $model->fee_type], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'lob_cd',
            'fee_type',
            'employer_remittable',
            'member_remittable',
        ],
    ]) ?>

</div>
