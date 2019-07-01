<?php

use yii\data\ActiveDataProvider;

/* @var $id mixed|string */
/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */

echo $this->render('../partials/_documentsummary', [
    'dataProvider' => $dataProvider,
    'id' => $id,
    'controller' => 'training-document',
    'permission' => 'manageTraining',
]);

