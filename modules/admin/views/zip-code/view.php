<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ZipCode */

$this->title = $model->zip_cd;
$this->params['breadcrumbs'][] = ['label' => 'Zip Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zip-code-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->zip_cd], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->zip_cd], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'zip_cd',
            'city',
            'island',
            'st',
        ],
    ]) ?>

</div>
