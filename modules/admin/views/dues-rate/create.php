<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\DuesRate */

$this->title = 'Create Dues Rate';
$this->params['breadcrumbs'][] = ['label' => 'Dues Rates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dues-rate-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
