<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\training\CredentialSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Credentials';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="credential-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Credential', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'credential',
            'display_seq',
            'card_descrip',
            'catg',
            // 'duration',
            // 'show_on_cert',
            // 'show_on_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
