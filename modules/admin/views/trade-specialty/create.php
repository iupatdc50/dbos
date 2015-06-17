<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\value\TradeSpecialty */

$this->title = 'Create Trade Specialty';
$this->params['breadcrumbs'][] = ['label' => 'Trade Specialties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trade-specialty-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
