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
	    	<div class="pull-right pad-rightlink">Print This Page</div>  
			<div class="header">
				<div id="logo-report" class="lion"></div>
				<div id="logo-title" class="lion-title">District Council 50<span>International Union of Painters and Allied Trades</span></div>
					
			</div>
		<hr>
			<div class="container">
 				<?= $content; ?>
			</div>
			<footer class="clearfix">
			    <div class="container">
       				<p class="pull-left">&copy; <?= date('Y') ?> 
       				   IUPAT District Council 50</a>. All rights reserved.
       				</p>
 			    </div>
			</footer>
		</div>
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
