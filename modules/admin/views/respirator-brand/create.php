<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\value\RespiratorBrand */

$this->title = 'Create Respirator Brand';
$this->params['breadcrumbs'][] = ['label' => 'Respirator Brands', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fee-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
