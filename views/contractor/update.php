<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $modelsAddress \yii\db/ActiveQuery */
/* @var $modelsPhone \yii\db/ActiveQuery */

$this->title = 'Update Contractor: ' . ' ' . $model->contractor;
$this->params['breadcrumbs'][] = ['label' => 'Contractors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->contractor, 'url' => ['view', 'id' => $model->license_nbr]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="contractor-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    	'modelsAddress' => $modelsAddress,
    	'modelsPhone' => $modelsPhone,
    ]) ?>

</div>
