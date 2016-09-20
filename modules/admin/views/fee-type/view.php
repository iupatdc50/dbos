<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\FeeType */

$this->title = $model->fee_type;
$this->params['breadcrumbs'][] = ['label' => 'Fee Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fee-type-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->fee_type], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->fee_type], [
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
            'fee_type',
            'descrip',
            'freq',
        	'is_assess',
        ],
    ]) ?>

</div>
