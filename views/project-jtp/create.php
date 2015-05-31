<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\project\jtp\Project */
/* @var $modelAddress app\models\project\Address */
/* @var $modelRegistration app\models\project\jtp\Registration */

$this->title = 'Create JTP Project';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    	'modelAddress' => $modelAddress,
    	'modelRegistration' => $modelRegistration,
    ]) ?>

    
    
</div>
