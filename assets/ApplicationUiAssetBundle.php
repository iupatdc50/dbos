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
            'js/stripe.js',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js',
	];
	public $depends = [
			'yii\web\YiiAsset',
			'yii\bootstrap\BootstrapAsset',
			//			'app\assets\AuditColumnAssetBundle',
	];
}
