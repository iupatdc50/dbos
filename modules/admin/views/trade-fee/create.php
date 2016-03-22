<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\TradeFee */

$this->title = 'Create Trade Fee';
$this->params['breadcrumbs'][] = ['label' => 'Trade Fees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trade-fee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
