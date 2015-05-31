<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\contractor\Signatory */

$this->title = 'Create Signatory';
$this->params['breadcrumbs'][] = ['label' => 'Signatories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="signatory-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
