<?php
return [ 
		'id' => 'dbos-console',
		'version' => '0.9.8.310',
		'basePath' => realPath( __DIR__ . '/../'),
		'controllerNamespace' => 'app\commands',
		'bootstrap' => ['log'],
		'components' => [ 
				'db' => require(__DIR__ . '/db.php'),
				'authManager' => require(__DIR__ . '/auth.php'),
				'log' => [
						'targets' => [
								'all_messages' => [
										'class' => 'yii\log\FileTarget',
										'categories' => ['application'],
										'levels' => ['info', 'warning', 'error'],
								],
						],
				],
				
		],
];