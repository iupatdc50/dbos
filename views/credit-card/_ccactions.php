<?php

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $submit_lbl string */

?>

<div class="row">
    <div class="col-xs-2 form-group">
        <label class="control-label" for="defer">Months to Defer</label>
        <input id="defer" name="defer" type="number" value="0" class="form-control required number" min="0" max="12">
        <p class="help-block help-block-error"></p>
    </div>
</div>

<br />

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


