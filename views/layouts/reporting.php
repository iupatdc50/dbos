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
				'heading' => '<i class="glyphicon glyphicon-book"></i> Reports Index',
				'items' => [
						[
								'label' => 'Membership',
								'visible' => Yii::$app->user->can('reportMember'),
								'items' => [
										[
												'label' => 'Member Extended Card', 
												'url'=>'/site/unavailable',
												
										],
										[
												'label' => 'Member Statuses', 
												'url'=>'/site/unavailable',
												
										],
										[
												'label' => 'PAC Reporting', 
												'url'=>'/site/unavailable',
												
										],
										[
												'label' => 'International Report', 
												'url'=>'/site/unavailable',										
										],
										['label' => 'Mailing Labels'],
								],
						],
						[
								'label' => 'Contractors',
								'visible' => Yii::$app->user->can('reportContractor'),
								'items' => [
										[
												'label' => 'Contractor Information', 
												'url'=>'/report/contractor-info',										
												'active' => (yii::$app->requestedRoute == 'report/contractor-info'),
										],
										['label' => 'Mailing Labels'],
										
								],
						],
						[
								'label' => 'Accounting',
								'visible' => Yii::$app->user->can('reportAccounting'),
								'items' => [
										[
												'label' => 'Cash Receipts', // All, contractors only
												'url'=>'/report/receipts-journal',
												'active' => (yii::$app->requestedRoute == 'report/receipts-journal'),
										],
										[
												'label' => 'Dues Status', // All, delinquent
												'url'=>'/report/dues-status',
												'active' => (yii::$app->requestedRoute == 'report/dues-status'),
										],
										[
												'label' => 'Employer Invoices',
												'visible' => Yii::$app->user->can('createInvoice'),
										],
								],
						],
						
						
						[
							'label' => 'Reports',
							'visible' => Yii::$app->user->can('reportAccounting'),
							'items' => [
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