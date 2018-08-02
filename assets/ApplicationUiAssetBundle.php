<?php
namespace app\assets;

use yii\web\AssetBundle;

class ApplicationUiAssetBundle extends AssetBundle
{
	public $sourcePath = '@app/assets/ui';
	public $css = [
			'css/main.css'
	];
	public $js = [
			'js/main.js',
            'js/accounting.js',
	];
	public $depends = [
			'yii\web\YiiAsset',
			'yii\bootstrap\BootstrapAsset',
			//			'app\assets\AuditColumnAssetBundle',
	];
}
