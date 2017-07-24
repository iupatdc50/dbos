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
												'items'=> [
														[
																'label' => 'Summary Report',
																'url'=>'/report/pac-summary',
																'active' => (yii::$app->requestedRoute == 'report/pac-summary'),
														],
														[
																'label' => 'Members not in PAC',
																'url'=>'/report/not-pac',
																'active' => (yii::$app->requestedRoute == 'report/not-pac'),
														],
														[
																'label' => 'Export Local PAC Data',
																'url'=>'/report/pac-export',
																'active' => (yii::$app->requestedRoute == 'report/pac-export'),
														],
														[
																'label' => 'Glaziers Contributions',
																'url'=>'/report/glaziers',
																'active' => (yii::$app->requestedRoute == 'report/glaziers'),
														],
												],
												
										],
										[
												'label' => 'International Report', 
												'url'=>'/site/unavailable',										
										],
										[
												'label' => 'Mailing Labels',
												'url'=>'/site/unavailable',
										],
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
										[
												'label' => 'Mailing Labels',
												'url'=>'/site/unavailable',
										],
										
								],
						],
						[
								'label' => 'Accounting',
								'visible' => Yii::$app->user->can('reportAccounting'),
								'items' => [
										[
												'label' => 'Cash Receipts', // All, contractors only
												'items' => [
														[
																'label' => 'Receipt Book Balances',
																'active' => (yii::$app->requestedRoute == 'report/receipts-journal'),
																'items' => [
																		[
																				'label' => 'Painters, Floor Layers, Tapers',
																				'url'=>['/report/receipts-journal'],
																				'active' => false,
																		],
																		[
																				'label' => 'Glaziers',
																				'url'=>['/report/receipts-journal', 'trade' => '1889'],
																				'active' => false,
																		],
																],
														],
														[
																'label' => 'International Report',
																'items' => [
																		[
																				'label' => 'Painters, Floor Layers, Tapers',
																				'url'=>'/site/unavailable',
																		],
																		[
																				'label' => 'Glaziers',
																				'url'=>'/site/unavailable',
																		],
																],
														],
												],
												
										],
										[
												'label' => 'Dues',
												'items' => [
														[
															'label' => 'Dues Status', 
															'url'=>'/report/dues-status',
															'active' => (yii::$app->requestedRoute == 'report/dues-status'),
														],
														[
															'label' => 'Delinquent Dues', 
															'url'=>'/report/delinquent-dues',
															'active' => (yii::$app->requestedRoute == 'report/delinquent-dues'),
														],
														
												],
										],
										[
												'label' => 'Employer Invoices',
												'visible' => Yii::$app->user->can('createInvoice'),
												'url'=>'/site/unavailable',
												
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