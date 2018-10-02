<?php

use yii\helpers\Html;
use app\helpers\Bar128Helper;

/* @var $this yii\base\View */
/* @var $model app\models\member\Member */

$suffix = is_null($model->suffix) ? '' : ', ' . $model->suffix;
$last_nm = $model->last_nm . $suffix;
/** @noinspection PhpUnhandledExceptionInspection */
$barcode = Bar128Helper::barcode_c128(stripcslashes($model->member_id));
?>

<div class="pvccard">
    <div class="pvchead">
        <div class="pvclogo pull-left"></div>
        <div class="center"><p class="pvciupat">International Union of Painters & Allied Trades<br />District Council 50<br />Honolulu, Hawaii<br />(808) 941-0991</p></div>
    </div>
    <div class="clearfix pvcside pull-left">
        <h1 class="vertical ">
            <?= 'LOCAL ' . strtoupper($model->currentStatus->lob->short_descrip) ?></h1>
    </div>
    <div class="pvcmain">
        <div class="center"><?= Html::img($model->imageUrl, ['class' => 'img-thumbnail', 'width' => '240', 'height' => '320',]) ?></div>
        <div class="center"><h3><?= $model->first_nm ?> <?= $model->middle_inits ?> <?= $last_nm ?></h3></div>
        <div class="center" style="padding-bottom: 0"><?= $barcode ?></div>
        <div class="center" style="padding-top: 0"><?= $model->member_id ?></div>
    </div>
</div>
