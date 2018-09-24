<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $modelsAddress yii\db\ActiveQuery */
/* @var $modelsPhone yii\db\ActiveQuery */
/* @var $modelsEmail yii\db\ActiveQuery */

$this->title = 'Update Contractor: ' . ' ' . $model->contractor;
$this->params['breadcrumbs'][] = ['label' => 'Contractors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->contractor, 'url' => ['view', 'id' => $model->license_nbr]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="contractor-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
//    		'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?= $this->render('_formfields', [
        'form' => $form,
        'model' => $model,
    	'modelSig' => null,
    ]) ?>

    <?php ActiveForm::end(); ?>

    <hr>
    <div class="rightside fifty-pct">

        <?= $this->render(
            '../partials/_addressgrid',
            [
                'modelsAddress' => $modelsAddress,
                'controller' => 'contractor-address',
                'relation_id' => $model->license_nbr,
            ]
        ) ?>

        <?= $this->render(
            '../partials/_phonegrid',
            [
                'modelsPhone' => $modelsPhone,
                'controller' => 'contractor-phone',
                'relation_id' => $model->license_nbr,
            ]
        ) ?>

        <?= $this->render('../contractor-email/_grid', [
                'modelsEmail' => $modelsEmail,
                'relation_id' => $model->license_nbr,
        ]) ?>

    </div>


</div>

<?= $this->render('../partials/_modal') ?>
