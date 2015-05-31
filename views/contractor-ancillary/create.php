<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\contractor\Ancillary */

$this->title = 'Create Ancillary';
$this->params['breadcrumbs'][] = ['label' => 'Ancillaries', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ancillary-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
