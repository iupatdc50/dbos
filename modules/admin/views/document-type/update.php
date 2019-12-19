<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\value\DocumentType */

$this->title = 'Update Document Type: ' . ' ' . $model->doc_type;
$this->params['breadcrumbs'][] = ['label' => 'Document Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->doc_type, 'url' => ['view', 'id' => $model->doc_type]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="document-type-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
