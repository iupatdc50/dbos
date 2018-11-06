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
    	<?php $this->head()?>
    	<?= Html::csrfMetaTags()?>
	</head>
	<body>
	<?php $this->beginBody()?>
        <?= $content; ?>
	<?php $this->endBody()?>
	</body>
	</html>
<?php $this->endPage()?>
