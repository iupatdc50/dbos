<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\member\MemberClass */

$this->title = 'Create Member Class';
$this->params['breadcrumbs'][] = ['label' => 'Member Classes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-class-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
