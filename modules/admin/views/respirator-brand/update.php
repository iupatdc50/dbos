<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\value\RespiratorBrand */

$this->title = 'Update Brand: ' . ' ' . $model->brand;
$this->params['breadcrumbs'][] = ['label' => 'Fee Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fee_type, 'url' => ['view', 'id' => $model->fee_type]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="fee-type-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
