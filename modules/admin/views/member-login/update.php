<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\member\MemberLogin */

$this->title = 'Update Member Login Record: ' . ' ' . $model->member_id;
$this->params['breadcrumbs'][] = ['label' => 'Login Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="login-record-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
