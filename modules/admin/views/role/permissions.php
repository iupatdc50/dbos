<?php

/* @var $this yii\web\View */
/* @var $permissions array */

$auth = '<span class="glyphicon glyphicon-ok text-success"></span>';
$other = '<span class="glyphicon glyphicon-minus text-danger"></span>';

?>

<div class="perm">

    <table class="table table-bordered">
        <thead>
        <tr><th>Description</th><th>Allowed</th></tr>
        </thead>
        <tbody>
        <?php foreach ($permissions as $key => $permission): ?>
            <tr><td><?= $key ?></td><td><?= ($permission) ? $auth : $other ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>



</div>
