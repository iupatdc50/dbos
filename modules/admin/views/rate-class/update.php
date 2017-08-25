<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\value\RateClass */

$this->title = 'Update Rate Class: ' . ' ' . $model->rate_class;
$this->params['breadcrumbs'][] = ['label' => 'Rate Classes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->rate_class, 'url' => ['view', 'id' => $model->rate_class]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="rate-class-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
