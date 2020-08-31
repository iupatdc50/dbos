<?php

/**
 * Member data entry form partial
 *
 * On create, a single address and single phone may be entered.
 */

use yii\db\ActiveQuery;

/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $modelsAddress ActiveQuery */
/* @var $modelsPhone ActiveQuery */
/* @var $modelsEmail ActiveQuery */
/* @var $modelsSpecialty ActiveQuery */
?>

	<hr>
		<?= $this->render(
	    		'../partials/_addressgrid',
	    		[
	    			'modelsAddress' => $modelsAddress,
	    			'controller' => 'member-address',
	    			'relation_id' => $model->member_id,	
	    		]
	    ) ?>
        <?= $this->render(
	    		'../partials/_phonegrid',
	    		[
	    			'modelsPhone' => $modelsPhone,
	    			'controller' => 'member-phone',
	    			'relation_id' => $model->member_id,	
	    		]
	    ) ?>
	    <?= $this->render(
	    		'../member-email/_grid',
	    		[
	    			'modelsEmail' => $modelsEmail,	
	    			'relation_id' => $model->member_id,
                    'count' => $model->emailCount,
	    		]
	    ) ?>
        <?= $this->render(
	    		'../member-specialty/_grid',
	    		[
	    			'modelsSpecialty' => $modelsSpecialty,	
	    			'relation_id' => $model->member_id,
	    		]
	    ) ?>
    
    <?= $this->render('../partials/_modal') ?>
	    
