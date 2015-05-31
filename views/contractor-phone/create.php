<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\contractor\Phone */

$this->title = 'Create Phone';
$this->params['breadcrumbs'][] = ['label' => 'Phones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="phone-create">

    <?= $this->render('../partials/_phoneform', [
        'model' => $model,
    ]) ?>

</div>
