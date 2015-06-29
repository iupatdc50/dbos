<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $modelAddress app\models\contractor\Address */

$this->title = 'Create Contractor';
$this->params['breadcrumbs'][] = ['label' => 'Contractors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contractor-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    	'modelSig' => $modelSig,
    	'modelAddress' => $modelAddress,
    	'modelPhone' => $modelPhone,
    ]) ?>

</div>
