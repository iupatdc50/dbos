<?php

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $submit_lbl string */

?>

<div class="form-group">
    <?= Html::submitButton($submit_lbl, [
        'class' => 'btn btn-success',
        'id' => 'submitter',

    ]); ?>
    <?= Html::button('Cancel', [
        'class' => 'btn btn-default',
        'data-dismiss' => 'modal',
    ]); ?>

</div>


