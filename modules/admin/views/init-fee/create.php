<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\InitFee */

$this->title = 'Create Init Fee';
$this->params['breadcrumbs'][] = ['label' => 'Init Fees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="init-fee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
