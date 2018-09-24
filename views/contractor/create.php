<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\contractor\Contractor */
/* @var $modelAddress app\models\contractor\Address */
/* @var $modelPhone app\models\contractor\Phone */
/* @var $modelEmail app\models\contractor\Email */
/* @var $modelSig app\models\contractor\Signatory */

$this->title = 'Create Contractor';
$this->params['breadcrumbs'][] = ['label' => 'Contractors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contractor-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
//    		'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?= $this->render('_formfields', [
            'form' => $form,
            'model' => $model,
    	    'modelSig' => $modelSig,
    ]) ?>

    <div class="rightside fifty-pct">
        <hr>
        <?= $this->render('../partials/_addressformfields',
            [
                'form'	=> $form,
                'address' => $modelAddress,
                'addressForm' => true,
            ]
        ) ?>
        <hr>
        <?= $this->render('../partials/_phoneformfields',
            [
                'form'	=> $form,
                'phone' => $modelPhone,
            ]
        ) ?>
        <hr>
        <?= $this->render('../contractor-email/_formfields', [
                'form' => $form,
                'email' => $modelEmail,
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
