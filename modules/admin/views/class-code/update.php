<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\member\ClassCode */

$this->title = 'Update Class Code: ' . ' ' . $model->member_class_cd;
$this->params['breadcrumbs'][] = ['label' => 'Member Class Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->member_class_cd, 'url' => ['view', 'id' => $model->member_class_cd]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="trade-specialty-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
