<?php
/**
 * @var string $content
 */
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\helpers\MenuHelper;
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
									['label' => 'User Accounts', 'icon' => 'user'],
									['label' => 'Roles (RBAC)', 'icon' => 'tasks'],
							],
						],
						[
							'label' => 'Accounting',
							'icon' => 'usd',
							'items' => [
								['label' => 'Rate Classes', 'url'=>'#'],
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
							],
						],
						[
							'label' => 'Support Tables',
							'icon' => 'wrench',
							'items' => [
								['label' => 'Address Types', 'url'=>'#'],
								['label' => 'Agreement Types', 'url'=>'#'],
								['label' => 'Phone Types', 'url'=>'#'],
								['label' => 'Shirt Sizes', 'url'=>'#'],
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
								['label' => 'Contact', 'icon'=>'phone', 'url'=>'#'],
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
		        <?= Breadcrumbs::widget([
		            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
		        ]) ?>
				<?= $content; ?>
			</div>
			
			</div>
			<footer class="footer clearfix">
			    <div class="container">
       				<p class="pull-left">&copy; <?= date('Y') ?> 
       				   <a href="http://www.dc50.org">IUPAT District Council 50</a>. All rights reserved.
       				</p>
 			    </div>
			</footer>
		</div>
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
