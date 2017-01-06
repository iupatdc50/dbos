<?php
/**
 * @var string $content
 */
use yii\helpers\Html;

?>

	<!DOCTYPE html>
	<head>
		<meta charset="<?= Yii::$app->charset ?>" />
		<title><?= Html::encode($this->title) ?></title>
    	<?php $this->head()?>
    	<?= Html::csrfMetaTags()?>
	</head>
	<body>

		<div class="header">
			<div id="title" class="title"><?= Yii::$app->name ?></div>	
		</div>
	
		<hr>
		<h2><span class="lbl-danger">System Temporarily Unavailable</span></h2>
		<p>Sorry!  DBOS is temporarily off-line for scheduled maintenance.  Please check back later.</p>
		<p>Questions?  Please contact your site administrator or tech support.</p>  
	</body>