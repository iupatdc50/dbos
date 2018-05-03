<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model app\models\accounting\Receipt  */
/* @var $this \yii\web\View */
/* @var $class string */

?>


<p>

        	<?php if(Yii::$app->user->can('updateReceipt') && ($model->void == 'F')): ?>
				<?= Html::a('Update', [
						'update',
						'id' => $model->id,
				], ['class' => 'btn btn-primary']) ?>
				<?php if(Yii::$app->user->can('deleteReceipt')) :?>
			        <?= Html::a('Void Receipt', ['void', 'id' => $model->id], [
			            'class' => 'btn btn-default',
			            'data' => [
			                'confirm' => 'Are you sure you want to void this receipt?  All allocations will be removed.',
			                'method' => 'post',
			            ],
			        ]) ?>
			    <?php endif; ?> 
			<?php endif; ?>

        	<?php if(Yii::$app->user->can('reportAccounting')): ?>
				<?=  Html::a(
				        '<i class="glyphicon glyphicon-print"></i>&nbsp;Print',
                        ['/receipt-' . $class . '/print-preview', 'id' => $model->id],
                        ['class' => 'btn btn-default', 'target' => '_blank'])
                ?>
			<?php endif; ?>
			
</p>
