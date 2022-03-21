<?php

use app\models\member\Member;

/* @var $this yii\web\View */
/* @var $member Member */

?>

<div id="new-card" class="modal-left">
    <div class="form-group">
        <label class="control-label" for="email">Receipt Email</label>
        <input id="email" name="email" type="email" value="<?= isset($member->defaultEmail) ? $member->defaultEmail->email : "info@dc50.org" ?>" class="form-control required email" placeholder="Receipt email">
        <p class="help-block help-block-error"></p>
    </div>
    <br />
    <div class="form-group">
        <label class="control-label" for="cardholder_nm">Cardholder Name</label>
        <input id="cardholder_nm" name="cardholder_nm" value="<?= $member->getFullName(false) ?>" class="form-control required" placeholder="Name on card" required>
        <p class="help-block help-block-error"></p>
    </div>
    <br />

    <input type="hidden" name="member_id" value="<?= $member->member_id ?>">
    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken; ?>">

    <div id="card-element" class="form-control">
        <!-- Elements will create input elements here -->
    </div>

    <!-- We'll put the error messages in this element -->
    <div id="card-errors" role="alert" class="help-block help-block-error"></div>
</div>

