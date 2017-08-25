<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\value\RateClass */

$this->title = 'Create Rate Class';
$this->params['breadcrumbs'][] = ['label' => 'Rate Classes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rate-class-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
