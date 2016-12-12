<?php

use yii\widgets\Breadcrumbs;
use kartik\widgets\SideNav;

?>

<?php $this->beginContent('@app/views/layouts/main.php'); ?>

			
	<div class="container hundred-pct">
		<div class="col-sm-3">
		<?php
			$type = SideNav::TYPE_DEFAULT;
			echo SideNav::widget([
				'type' => $type,
				'heading' => 'Operations',
				'items' => [
						[
							'label' => 'Employer Invoices', 
							'visible' => Yii::$app->user->can('createInvoice'),
							'icon' => '',
							'items' => [
								['label' => 'All Employers'],		
								['label' => 'Choose Employer'],		
							],
						],
						[
							'label' => 'Reports',
							'visible' => Yii::$app->user->can('reportAccounting'),
							'icon' => 'book',
							'items' => [
								[
										'label' => 'Receipt Book Balance', 
										'url'=>'/site/unavailable',										
								],
								[
										'label' => 'Member Statuses', 
										'url'=>'/site/unavailable',
										
								],
								[
										'label' => 'Arrears Report', 
										'url'=>'/site/unavailable',
										
								],
								[
										'label' => 'Generate PAC Export', 
										'url'=>'/site/unavailable',
										
								],
							],
						],
					],
			]);
			
			?>
		</div>
		<div class="col-sm-9">
			<?= $content; ?>
		</div>
	
	


<?php $this->endContent(); ?>