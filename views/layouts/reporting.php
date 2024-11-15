<?php

use kartik\widgets\SideNav;

/** @var string $content */

?>

<?php $this->beginContent('@app/views/layouts/main.php'); ?>

			
	<div class="container hundred-pct">
		<div class="col-sm-3">
		<?php
			$type = SideNav::TYPE_DEFAULT;
        /** @noinspection PhpUnhandledExceptionInspection */
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
                                                'label' => 'Active Members List',
                                                'url'=>'/report/active-members',
//                                                'active' => (yii::$app->requestedRoute == 'report/active-members'),
                                        ],
                                        [
                                                'label' => 'Inactive Members List',
                                                'url'=>'/report/inactive-members',
                                                'active' => (yii::$app->requestedRoute == 'report/inactive-members'),
                                        ],
										[
                                            'label' => 'Members Out-of-Work List',
                                            'url'=>'/report/unemployed-members',
                                            'active' => (yii::$app->requestedRoute == 'report/unemployed-members'),
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
                                                                'label' => 'Painters/FL/Tapers Contributions',
                                                                'url'=>'/report/pac-contributions',
                                                                'active' => (yii::$app->requestedRoute == 'report/pac-contributions'),
                                                        ],
														[
																'label' => 'Glaziers Contributions',
																'url'=>'/report/glaziers',
																'active' => (yii::$app->requestedRoute == 'report/glaziers'),
														],
												],
												
										],
										[
												'label' => 'Not Employed by Payor', 
												'url'=>'/report/wrong-payor',	
												'active' => (yii::$app->requestedRoute == 'report/wrong-payor'),
										],
                                        [
                                                'label' => 'IMSe Audit',
                                                'url'=>'/report/imse-audit',
                                                'active' => (yii::$app->requestedRoute == 'report/imse-audit'),
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
                                        [
                                                'label' => 'Hours Summary',
                                                'items' => [
                                                    [
                                                        'label' => '1791: Painters',
                                                        'url' => ['report/hours-summary', 'lob_cd' => '1791'],
                                                    ],
                                                    [
                                                        'label' => '1889: Glaziers',
                                                        'url' => ['report/hours-summary', 'lob_cd' => '1889'],
                                                    ],
                                                    [
                                                        'label' => '1926: Floorlayers',
                                                        'url' => ['report/hours-summary', 'lob_cd' => '1926'],
                                                    ],
                                                    [
                                                        'label' => '1944: Tapers',
                                                        'url' => ['report/hours-summary', 'lob_cd' => '1944'],
                                                    ],
                                                ],
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
																'url' => '/report/international',
																'active' => (yii::$app->requestedRoute == 'report/international'),
														],
                                                        [
                                                                'label' => 'Universal File',
                                                                'url' => '/report/universal',
                                                                'active' => (yii::$app->requestedRoute == 'report/universal'),
                                                        ],
														[
																'label' => 'Payment Method Summary',
																'url' => '/report/payment-method',
																'active' => (yii::$app->requestedRoute == 'report/payment-method'),
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
															'label' => 'Candidates for Suspend Action',
															'url'=>'/report/candidate-suspends',
															'active' => (yii::$app->requestedRoute == 'report/candidate-suspends'),
														],
														[
															'label' => 'Candidates for Drop Action',
															'url'=>'/report/candidate-drops',
															'active' => (yii::$app->requestedRoute == 'report/candidate-drops'),
														],
                                                        [
                                                            'label' => 'Yearly Totals',
                                                            'url'=>'/report/yearly-totals',
                                                            'active' => (yii::$app->requestedRoute == 'report/yearly-totals'),
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
						        'label' => 'Training',
                                'visible' => Yii::$app->user->can('reportTraining'),
                                'items' => [
                                    [
                                        'label' => 'Active Members List',
                                        'url'=>'/report/training-members',
                                    ],
                                    [
                                        'label' => 'Training History',
                                        'url'=>'/report/training-history',
                                        'active' => (yii::$app->requestedRoute == 'report/training-history'),
                                    ],
                                    [
                                        'label' => 'Expired Certification Classes',
                                        'url'=>'/report/expired-classes',
                                        'active' => (yii::$app->requestedRoute == 'report/expired-classes'),
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
    </div>
	


<?php $this->endContent(); ?>