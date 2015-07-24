<?php
return [
		
				'db' => require(__DIR__ . (YII_ENV_TEST ? '/db-test.php' : '/db.php')),
				'urlManager' => [
						'enablePrettyUrl' => true,
						'showScriptName' => false,
						'rules' => [
						],
				],
//				'authManager' => require(__DIR__ . '/auth.php'),
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
										'levels' => [
//		                    					'trace',
//		                    					'info',
												'warning',
												'error',
										],
								],
								'problems' => [
										'class' => \yii\log\EmailTarget::className(),
										'levels' => \yii\log\Logger::LEVEL_ERROR,
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