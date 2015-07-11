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
				<div id="logo" class="logo"></div>
				<div id="title" class="title"><?= Yii::$app->name ?></div>	
			</div>
		
			<div class="col-sm-2"></div>
			<div class="col-sm-6">
				<?= $content; ?>
			</div>
			
		</div>
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
