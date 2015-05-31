<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\value\BillRate */

$this->title = 'Create Bill Rate';
$this->params['breadcrumbs'][] = ['label' => 'Bill Rates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bill-rate-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
