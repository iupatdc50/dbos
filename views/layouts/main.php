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
	            	$menuItems[] = ['label' => 'Membership', 'url' => ['/member/'], 
	            			'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'member'),
	            	];
	                $menuItems[] = ['label' => 'Contractors', 'url' => ['/contractor/'],
	                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'contractor'),
	                ];
	                $menuItems[] = ['label' => 'Projects',
	                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'project'),
	                		'items' => [
	                				['label' => 'LMA Projects', 'url' => ['/project-lma/']],
	                				['label' => 'JTP Projects', 'url' => ['/project-jtp/']],
	                		],
	                ];
	                $menuItems[] = ['label' => 'Accounting', 'url' => ['/site/unavailable'],
	                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'accounting'),
	                ];
	                $menuItems[] = ['label' => 'Training', 'url' => ['/site/unavailable'],
	                		'active' => MenuHelper::isItemActive(yii::$app->requestedRoute, 'training'),
	                ];
	                $menuItems[] = ['label' => 'Admin', 'url' => ['/admin'],
	                ];
	                $menuItems[] = [
	                	'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
	                    'url' => ['/site/logout'],
	                    'linkOptions' => ['data-method' => 'post'],
	                ];
	            }
	            echo Nav::widget([
	                'options' => ['class' => 'navbar-nav navbar-right'],
	            	'items' => $menuItems,
	            ]);
	            NavBar::end();
	        ?>

			<div class="container">
	        <?= Breadcrumbs::widget([
	            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
	        ]) ?>
 			<?= $content; ?>
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
