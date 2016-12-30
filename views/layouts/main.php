<?php
/**
 * @var string $content
 */
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\helpers\MenuHelper;


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
			<?php if (MenuHelper::isItemActive(yii::$app->requestedRoute, 'site') || (yii::$app->requestedRoute == '')): ?>
				<div class="logo"></div>
				<div class="title"><?= Yii::$app->name ?> </div>	
			<?php else: ?>
				<div id="logo-nothome" class="logo"></div>
				<div id="title-nothome" class="title"><?= Yii::$app->name ?></div>	
			<?php endif; ?>			
			</div>
		
	        <?php
	            NavBar::begin([
	                'brandLabel' => 'District Council 50',
	                'brandUrl' => Yii::$app->homeUrl,
	                'options' => [
	                    'class' => 'navbar-inverse',
	                ],
	            ]);
	            $menuItems = [
	                ['label' => 'Home', 'url' => ['/site/index']],
	            ];
	            if (Yii::$app->user->isGuest) {
	                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
	            } else {
	            	if(Yii::$app->user->can('browseMember'))
		            	$menuItems[] = [
		            			'label' => 'Membership', 'url' => ['/member/'], 
		            			'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'member'),
		            	];
	            	if(Yii::$app->user->can('browseContractor'))
		            	$menuItems[] = [
		                		'label' => 'Contractors', 'url' => ['/contractor/'],
		                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'contractor'),
		                ];
	            	if(Yii::$app->user->can('browseProject'))
		            	$menuItems[] = [
		                		'label' => 'Projects',
		                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'project'),
		                		'items' => [
		                				['label' => 'LMA Projects', 'url' => ['/project-lma/']],
		                				['label' => 'JTP Projects', 'url' => ['/project-jtp/']],
		                		],
		                ];
	            	if(Yii::$app->user->can('browseReceipt'))
		            	$menuItems[] = [
		                		'label' => 'Accounting', 'url' => ['/accounting/'],
		                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'accounting'),
		                ];
	            	if(Yii::$app->user->can('browseTraining'))
		            	$menuItems[] = [
		                		'label' => 'Training', 'url' => ['/site/unavailable'],
		                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'training'),
		                ];
		            if(Yii::$app->user->can('manageSupport'))		 
		                $menuItems[] = [
		                		'label' => 'Admin', 'url' => ['/admin'],
		                ];
	                $menuItems[] = [
	                	'label' => 'Account: ' . Yii::$app->user->identity->first_nm,
	                	'items' => [
	                			[
	                					'label' => 'Logout',		
	                    				'url' => ['/site/logout'],
	                    				'linkOptions' => ['data-method' => 'post'],
	                			],
	                			[
			                			'label' => 'Reset Password',
			                			'url' => ['/user/reset-pw'],
	                			],
	                			
	                	],
	                ];
	            }
	            echo Nav::widget([
	                'options' => ['class' => 'navbar-nav navbar-right'],
	            	'items' => $menuItems,
	            ]);
	            NavBar::end();
	        ?>

	        
	        
			<div class="container ninety-pct">
	        <?= Breadcrumbs::widget([
	            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
	        ]) ?>

	        <?php 
				foreach (Yii::$app->session->getAllFlashes() as $key => $messages) {
					$message = (is_array($messages)) ? implode(', ', $messages) : $messages;
					echo '<div class="flash-' . $key . '">' . $message . '</div>';
				} ?>
	        
	        <?= $content; ?>
			</div>
			<footer class="footer clearfix">
			    <div class="container">
       				<p class="pull-left">&copy; <?= date('Y') ?> 
       				   <a href="http://www.dc50.org">IUPAT District Council 50</a>. All rights reserved.
       				</p>
       				<?php if (!Yii::$app->user->isGuest): ?>
	        			<p class="pull-right">Last logged in: <?= Yii::$app->session->get('user.last_login'); ?></p>
    				<?php endif; ?>	
 			    </div>
			</footer>
		</div>
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
