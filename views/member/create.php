<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $modelAddress app\models\member\Address */
/* @var $modelPhone app\models\member\Phone */
/* @var $modelEmail app\models\member\Email */
/* @var $modelStatus app\models\member\Status */
/* @var $modelClass app\models\member\MemberClass */

$this->title = 'Create Member';
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    	'modelAddress' => $modelAddress,
    	'modelPhone' => $modelPhone,
    	'modelEmail' => $modelEmail,
    	'modelStatus' => $modelStatus,
    	'modelClass' => $modelClass,
    ]) ?>

</div>
