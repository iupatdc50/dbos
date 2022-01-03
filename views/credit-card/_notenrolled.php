<?php

/* @var $member_id string */

use yii\bootstrap\Html;
use yii\helpers\Url;

?>

<div class="forty-pct">
    <h5>Not Currently Enrolled</h5>
    <p>Member is not currently enrolled in automatic dues payment. Click <span class="emphasize">Enroll</span> button to configure auto-pay.</p>
    <?= Html::button('<i class="glyphicon glyphicon-edit"></i>&nbsp;Enroll',
        ['value' => Url::to(['enroll', 'id'  => $member_id]),
            'id' => 'enrollButton',
            'class' => 'btn btn-default btn-modal',
            'data-title' => 'Auto-Pay Enrollment',
        ])
    ?>
</div>
