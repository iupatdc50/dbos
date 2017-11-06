<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $modelReceipt app\models\accounting\Receipt  */

?>


<div class="pull-right">
    		<?php if ($modelReceipt->outOfBalance != 0.00): ?>
    			<span class="lbl-danger"><?= Html::encode('Out of Balance: ' . number_format($modelReceipt->outOfBalance, 2)); ?></span>
			<?php endif ?>
</div>
<div><p>
		<?php if ($modelReceipt->outOfBalance == 0.00): ?>
            	<?= Html::a('Post', ['post', 'id' => $modelReceipt->id], ['class' => 'btn btn-primary']) ?>
        <?php else: ?>
            	<?=  Html::button('<i class="glyphicon glyphicon-check"></i>&nbsp;Balance', 
							[
									'value' => Url::to(["balance", 'id' => $modelReceipt->id]),
									'id' => 'balanceButton',
									'class' => 'btn btn-default btn-modal',
									'data-title' => 'Adjustments',
							]); ?>
        <?php endif ?>
            	<?= Html::a('Cancel', ['cancel', 'id' => $modelReceipt->id], [
       	            		'class' => 'btn btn-danger',
	            			'data' => [
	                			'confirm' => "Are you sure you want to proceed? You will lose the data you entered, and receipt number: `{$modelReceipt->id}` cannot be reused.",
	                			'method' => 'post',
	            			],
	           	]) ?>
</p></div>

