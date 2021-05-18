<?php

use yii\helpers\Html;
use app\helpers\OptionHelper;


/* @var $this yii\web\View */
/* @var $model app\models\accounting\receipt */
/* @var $membProvider yii\data\ActiveDataProvider */
/* @var $searchMemb app\models\accounting\AllocatedMemberSearch */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$void_banner = ($model->void == OptionHelper::TF_TRUE) ? ' <span class="lbl-danger">** VOID **</span>' : '';

?>
<div class="receipt-view">

    <h1><?= Html::encode('Receipt: ' . $this->title)  . $void_banner ?></h1>

    <div class="leftside forty-pct">

        <?= $this->render('../receipt/_viewtoolbar', ['model' => $model]); ?>
	    <?= $this->render('../receipt/_detail', ['modelReceipt' => $model]); ?>


        <?php if(isset($model->billPayment)): ?>
            <?=  Html::a(
                '<i class="glyphicon glyphicon-list-alt"></i>&nbsp;Show Transmittal',
                ['/bill-payment/show-transmittal', 'xlsx_name' => $model->billPayment->transmittal],
                ['class' => 'btn btn-default', 'target' => '_blank', 'data-pjax'=>"0"])
            ?>
        <?php endif; ?>

    </div>

    <div class="rightside fiftyfive-pct">

        <?= $this->render('../receipt/_viewgridmulti', [
                'model' => $model,
                'searchMemb' => $searchMemb,
                'membProvider' => $membProvider,
        ]); ?>

    </div>
	
</div>