<?php
use yii\helpers\Html;

/* @var $email string */
?>

<h3>Temporary Password Sent</h3>
<hr>
<p>A temporary password was sent to <?= $email ?>.  You will prompted to set a permament password after logging in
with this password for the first time.</p>

<p>If you did not receive the email, please check your spam and junk mail settings before attempting another reset.</p>
 
<br /><br />
<p><?= Html::a('Return to Login', '/site', ['class' => 'btn btn-primary']); ?></p>
