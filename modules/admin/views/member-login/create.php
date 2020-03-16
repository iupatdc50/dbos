<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\member\MemberLogin */

$this->title = 'Create Member Login Record';
$this->params['breadcrumbs'][] = ['label' => 'User Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="login-record-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
