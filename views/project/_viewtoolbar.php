<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model app\models\accounting\Receipt  */

?>

<p>
        	<?php if(Yii::$app->user->can('manageProject')): ?>
				<?= Html::a('Update', ['update', 'id' => $model->project_id], ['class' => 'btn btn-primary']) ?>
				<?php if(Yii::$app->user->can('deleteProject')) :?>
					<?= Html::a('Delete', ['delete', 'id' => $model->project_id], [
            			'class' => 'btn btn-danger',
           				'data' => [
                			'confirm' => 'Are you sure you want to delete this item?',
                			'method' => 'post',
            			],
       				 ]) ?>
       			<?php endif; ?>
       			<?php if($model->project_status == 'A'): ?>
		        <?= Html::button('<i class="glyphicon glyphicon-minus-sign"></i>&nbsp;Cancel Project', [
		        		'value' => Url::to(["cancel", 'id'  => $model->project_id]),
		            	'id' => 'cancelButton',
		            	'class' => 'btn btn-default btn-modal',
		            	'data-title' => 'Cancellation',	
		        ]) ?>
		        <?php endif; ?>
		    <?php endif; ?> 
</p>
