<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\training\Credential */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Credentials', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="credential-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'credential',
            'display_seq',
            'card_descrip',
            'catg',
            'duration',
            'show_on_cert',
            'show_on_id',
        ],
    ]) ?>

</div>
