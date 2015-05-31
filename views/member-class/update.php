<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\member\MemberClass */

$this->title = 'Update Member Class: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Member Classes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="member-class-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
