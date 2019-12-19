<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\value\DocumentType */

$this->title = $model->doc_type;
$this->params['breadcrumbs'][] = ['label' => 'Document Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-type-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->doc_type], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->doc_type], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'doc_type',
            'catg',
        ],
    ]) ?>

</div>
