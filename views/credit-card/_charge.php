<?php

/* @var $this yii\web\View */
/* @var $total_due float */
/* @var $has_ccg boolean */

?>

        <div class="row">
            <div class="col-xs-2">
                <label for="currency">Currency</label>
                <input id="currency" name="currency" class="form-control" value="usd" readonly>
            </div>
            <div class="col-xs-10 form-group">
                <label class="control-label" for="charge">Charge</label>
                <input id="charge" name="charge" class="form-control required number" type="number" value="<?= $total_due ?>">
            </div>
        </div>
        <br />
        <?php if ($has_ccg > 0): ?>
            <br />
            <div class="form-group">
                <label for="other_local">Receiving Local</label>
                <input id="other_local" name="other_local" class="form-control field-room digits">
            </div>
        <?php endif; ?>



