<?php

/* @var $this yii\web\View */
/* @var $model app\models\accounting\StagedAllocation */
/* @var $license_nbr string */

?>

<div class="allocation-reassign">

    <?= $this->render('../partials/_empllookup', [
        'model' => $model,
    	'label' => 'Reassign',
    ]) ?>

    
    
</div>
