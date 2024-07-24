<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\value\TradeSpecialty */

$this->title = 'Create Class Code';
$this->params['breadcrumbs'][] = ['label' => 'Member Class Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="class-code-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
