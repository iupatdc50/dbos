<?php
/**
 * @var string $content
 */
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use kartik\widgets\SideNav;



app\assets\ApplicationUiAssetBundle::register($this);
?>
<?php $this->beginPage(); ?>
	<!DOCTYPE html>
	<html lang="<?= Yii::$app->language ?>">
	<head>
		<meta charset="<?= Yii::$app->charset ?>" />
		<title><?= Html::encode($this->title) ?></title>
    	<?php $this->head()?>
    	<?= Html::csrfMetaTags()?>
	</head>
	<body>
	<?php $this->beginBody()?>
	    <div class="wrap">   
			<div class="header">
				<div id="logo-nothome" class="logo"></div>
				<div id="title-nothome" class="title"><?= Yii::$app->name ?> </div>	
			</div>
		
	        <?php
	            NavBar::begin([
	                'brandLabel' => 'District Council 50 Admin',
	                'brandUrl' => Yii::$app->homeUrl,
	                'options' => [
	                    'class' => 'navbar-default',
	            ],
	            ]);
	            $menuItems = [
	                ['label' => 'Return to Main Site', 'url' => ['/site/index']],
	            ];
                /** @noinspection PhpUnhandledExceptionInspection */
                echo Nav::widget([
	                'options' => ['class' => 'navbar-nav navbar-right'],
	                'items' => $menuItems,
	            ]);
	            NavBar::end();
	        ?>

			<div class="container">
			<div class="col-sm-3">
			<?php
			
			$type = SideNav::TYPE_DEFAULT;
            /** @noinspection PhpUnhandledExceptionInspection */
            echo SideNav::widget([
				'type' => $type,
				'heading' => 'Operations',
				'items' => [
						[
							'url' => '/admin',
							'label' => 'Home',
							'icon' => 'home'
						],
						[
							'label' => 'Security',
							'icon' => 'lock',
							'items' => [
									[
									        'label' => 'User Accounts',
                                            'url'=>'/admin/user',
                                            'icon' => 'user',
                                            'active' => (yii::$app->requestedRoute == 'admin/user'),
                                    ],
                                    [
                                        'label' => 'Member Login Accounts',
                                        'url'=>'/admin/member-login',
                                        'icon' => 'globe',
                                        'active' => (yii::$app->requestedRoute == 'admin/member-login'),
                                    ],
									[
									        'label' => 'Roles (RBAC)',
                                            'icon' => 'tasks',
                                            'url'=>'/admin/default/unavailable',
                                    ],
							],
						],
						[
							'label' => 'Accounting',
							'icon' => 'usd',
							'items' => [
								[		
										'label' => 'Rate Classes',
										'url'=>'/admin/rate-class',
										'active' => (yii::$app->requestedRoute == 'admin/rate-class'),
								],
								[
										'label' => 'Fee Types', 
										'url' => '/admin/fee-type',
										'active' => (yii::$app->requestedRoute == 'admin/fee-type'),
								],
								[
										'label' => 'Trade Fee Options', 
										'url' => '/admin/trade-fee',
										'active' => (yii::$app->requestedRoute == 'admin/trade-fee'),
								],
								[
									'label' => 'Initiation Fees (APF)', 
									'url'=> '/admin/init-fee', 
									'active' => (yii::$app->requestedRoute == 'admin/init-fee')
								],
                                [
                                    'label' => 'Dues Rates',
                                    'url'=> '/admin/dues-rate',
                                    'active' => (yii::$app->requestedRoute == 'admin/dues-rate')
                                ],
                                [
                                    'label' => 'Contribution Rates',
                                    'url'=> '/admin/contribution',
                                    'active' => (yii::$app->requestedRoute == 'admin/contribution')
                                ],
							],
						],
                        [
                            'label' => 'Training',
                            'icon' => 'education',
                            'items' => [
                                [
                                        'label' => 'Work Processes',
                                        'url'=>'/admin/default/unavailable',
                                ],
                                [
                                        'label' => 'Credentials',
                                        'url'=>'/admin/default/unavailable',
                                ],
                                [
                                        'label' => 'Respirator Brands',
                                        'url'=>'/admin/respirator-brand',
 										'active' => (yii::$app->requestedRoute == 'admin/respirator-brand'),
                               ],
                            ],
                        ],
						[
							'label' => 'Support Tables',
							'icon' => 'wrench',
							'items' => [
								[
								        'label' => 'Address Types',
                                        'url'=>'/admin/default/unavailable',
                                ],
								[
								        'label' => 'Agreement Types',
                                        'url'=>'/admin/default/unavailable',
                                ],
                                [
                                    'label' => 'Document Types',
                                    'url' => '/admin/document-type',
                                    'active' => (yii::$app->requestedRoute == 'admin/document-type'),
                                ],
                                [
                                    'label' => 'Member Class Codes',
                                    'url' => '/admin/class-code',
                                    'active' => (yii::$app->requestedRoute == 'admin/class-code'),
                                ],
								[
								        'label' => 'Phone Types',
                                        'url'=>'/admin/default/unavailable',
                                ],
								[
								        'label' => 'Shirt Sizes',
                                        'url'=>'/admin/default/unavailable',
                                ],
								[
										'label' => 'Trade Specialties', 
										'url' => '/admin/trade-specialty',
										'active' => (yii::$app->requestedRoute == 'admin/trade-specialty'),
								],
								[
										'label' => 'Zip Codes', 
										'url'=>'/admin/zip-code',
										'active' => (yii::$app->requestedRoute == 'admin/zip-code'),
								],
							],
						],
						[
							'label' => 'Help',
							'icon' => 'question-sign',
							'items' => [
								[
										'label' => 'About', 
										'icon'=>'info-sign', 
										'url'=>'/admin/default/about',
										'active' => (yii::$app->requestedRoute == 'admin/default/about'),
								],
/*
								[
								        'label' => 'Contact',
                                        'icon'=>'phone',
                                    'url'=>'#'
                                ],
*/
								[
										'label' => 'Environment', 
										'icon' => 'cog', 
										'url'=>'/admin/default/info',
										'active' => (yii::$app->requestedRoute == 'admin/default/info'),
								],
							],
						],
					],
			]);
			
			?>
			
			</div>
			
			<div class="col-sm-9">
		        <?= /** @noinspection PhpUnhandledExceptionInspection */
                Breadcrumbs::widget([
		            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
		        ]) ?>
                <?php
                foreach (Yii::$app->session->getAllFlashes() as $key => $messages) {
                    $message = (is_array($messages)) ? implode(', ', $messages) : $messages;
                    echo '<div class="flash-' . $key . '">' . $message . '</div>';
                } ?>
				<?= $content; ?>
			</div>
			
			</div>
		</div>
        <footer class="footer">
            <div class="container">
                <p class="pull-left">&copy; <?= date('Y') ?>
                    <a href="http://www.dc50.org">IUPAT District Council 50</a>. All rights reserved.
                </p>
            </div>
        </footer>
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
