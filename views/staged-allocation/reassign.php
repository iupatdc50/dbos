<?php

/* @var $this yii\web\View */
/* @var $model app\models\accounting\StagedAllocation */
/* @var $license_nbr string */

?>

<div class="allocation-add">

    <?= $this->render('_empllookup', [
        'model' => $model,
    	'license_nbr' => $license_nbr,
    	'label' => 'Re-Assign',
    ]) ?>

    
    
</div>
