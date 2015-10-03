<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $modelsAddress \yii\db/ActiveQuery */
/* @var $modelsPhone \yii\db/ActiveQuery */

$this->title = 'Update Member: ' . ' ' . $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fullName, 'url' => ['view', 'id' => $model->member_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="member-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    	'modelsAddress' => $modelsAddress,
    	'modelsPhone' => $modelsPhone,
    	'modelsEmail' => $modelsEmail,
    	'modelsSpecialty' => $modelsSpecialty,
    ]) ?>

</div>
