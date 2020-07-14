<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Contribution */

$this->title = 'Update Contribution Rate';
$this->params['breadcrumbs'][] = ['label' => 'Contribution Rates', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="fee-type-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
