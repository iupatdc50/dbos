<?php
/**
 * @var string $content
 */
use yii\helpers\Html;


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
				<div id="title-nothome" class="title"><?= Yii::$app->name ?></div>	
			</div>
		<hr>
			<div class="container">
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
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
