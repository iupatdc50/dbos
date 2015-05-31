<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\project\lma\Project */
/* @var $modelsAddress \yii\db/ActiveQuery */

$this->title = 'Update LMA Project: ' . ' ' . $model->project_nm;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->project_nm, 'url' => ['view', 'id' => $model->project_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="project-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    	'modelsAddress' => $modelsAddress,
    ]) ?>

</div>
