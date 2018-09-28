<?php

use yii\helpers\Html;

/* @var $this yii\base\View */
/* @var $model app\models\member\Member */

$suffix = is_null($model->suffix) ? '' : ', ' . $model->suffix;
$last_nm = $model->last_nm . $suffix;
?>

<div class="pvccard">
    <div class="pvchead">
        <div class="pvclogo pull-left"></div>
        <div class="center"><p class="pvciupat">International Union of<br />Painters & Allied trades<br />District Council 50<br />Honolulu, Hawaii<br />(808) 941-0991</p></div>
    </div>
    <div class="clearfix pvcside pull-left">
        <h1 class="vertical ">
            <?= 'LOCAL ' . strtoupper($model->currentStatus->lob->short_descrip) ?></h1>
    </div>
    <div class="pvcmain">
        <div class="center"><?= Html::img($model->imageUrl, ['class' => 'img-thumbnail', 'width' => '210', 'height' => '280',]) ?></div>
        <div class="center"><h4><?= $model->first_nm ?> <?= $model->middle_inits ?> <?= $last_nm ?></h4></div>
        <div class="center"><h5><?= $model->member_id ?></h5></div>
    </div>
</div>
