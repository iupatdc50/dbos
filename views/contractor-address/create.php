<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\contractor\Address */

$this->title = 'Create Address';
$this->params['breadcrumbs'][] = ['label' => 'Addresses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="address-create">

    <?= $this->render('../partials/_addressform', [
        'model' => $model,
    ]) ?>

</div>
