<?php
/** @noinspection PhpIncludeInspection */

use yii\log\EmailTarget;
use yii\log\Logger;

return [
                'cache' => [
                    'class' => 'yii\caching\MemCache',
                    'useMemcached' => true,
                ],
				'db' => require(__DIR__ . (YII_ENV_TEST ? '/db-test.php' : '/db.php')),
				'urlManager' => [
						'enablePrettyUrl' => true,
						'showScriptName' => false,
						'rules' => [
						],
				],
				'authManager' => require(__DIR__ . '/auth.php'),
				'formatter' => [
						'timeZone' => 'UTC',
						'dateFormat' => 'php:m/d/Y',
						'datetimeFormat' => 'php: M d, Y h:i a',
				],
				'mail' => [
						'class' => yii\swiftmailer\Mailer::className(),
						'messageConfig' => [
								'charset' => 'UTF-8',
								'from' => 'dbosadmin@objectpac.com',
						],
						'transport' => [
								'class' => 'Swift_MailTransport',
						],
				],
				'log' => [
						'traceLevel' => YII_DEBUG ? 3 : 0,
						'targets' => [
								'all_messages' => [
										'class' => 'yii\log\FileTarget',
										'categories' => ['application'],
										'levels' => YII_DEBUG ? ['trace', 'info', 'warning', 'error'] : ['warning', 'error'],
								],
								'problems' => [
										'class' => EmailTarget::className(),
										'levels' => Logger::LEVEL_ERROR,
										'message' => [
												'to' => 'support@dc50.org'
										]
								]
						],
				],
				'request' => [
						'enableCookieValidation' => true,
						'cookieValidationKey' => 'ObjectPac'
				],
		
				'user' => [
						'identityClass' => 'app\models\user\User',
						'enableAutoLogin' => true,
				],
		
		
];