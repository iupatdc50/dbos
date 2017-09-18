<?php
use kartik\datecontrol\Module;

$env = !YII_ENV_PROD ? ' (' .YII_ENV . ')' : '';
$config = [ 
		'id' => 'dbos',
		'name' => 'DC50 Business Office Support' . $env,
		'version' => '0.9.9.200',
		'basePath' => realpath ( __DIR__ . '/../' ),
		'modules' => [ 
			    'datecontrol' => [
			        'class' => 'kartik\datecontrol\Module',
			 
			        // format settings for displaying each date attribute
			        'displaySettings' => [
			            Module::FORMAT_DATE => 'php:n/j/Y',
			            Module::FORMAT_TIME => 'php:h:i A',
			            Module::FORMAT_DATETIME => 'php:m/d/Y h:i:s A',
			        ],
			 
			        // format settings for saving each date attribute
			        'saveSettings' => [
			            Module::FORMAT_DATE => 'php:Y-m-d',
			            Module::FORMAT_TIME => 'php:H:i',
			            Module::FORMAT_DATETIME => 'php:Y-m-d H:i:s',
			        ],
			    	'displayTimezone' => 'UTC',
			    	'saveTimezone' => 'UTC',
			        // automatically use kartik\widgets for each of the above formats
			        'autoWidget' => true,

			    	'autoWidgetSettings' =>  [
			    			'date' => ['pluginOptions' => [
		                		'autoclose' => true,
		                		'todayHighlight' => true,
		                		'todayBtn' => false,
					    		'options' => ['placeholder' => 'mm/dd/yyyy'],
           		 			]],
			    			'time' => ['pluginOptions' => [
			    					'template' => false,
			    					'defaultTime' => '12:00 AM',
			    			]],
			    	],
			    ],
				'gridview' => [
						'class' => '\kartik\grid\Module',
				],
				'reportico' => [
						'class' => 'reportico\reportico\Module' ,
						'controllerMap' => [
								'reportico' => 'reportico\reportico\controllers\ReporticoController',
								'mode' => 'reportico\reportico\controllers\ModeController',
								'ajax' => 'reportico\reportico\controllers\AjaxController',
						],
				],				
				'admin' => [
						'class' => 'app\modules\admin\AdminModule',
				],
		],
		'components' => require (__DIR__ . '/components.php'),
		'extensions' => require (__DIR__ . '/../vendor/yiisoft/extensions.php'), 
		'params' => [
				'imageDir' => DIRECTORY_SEPARATOR . 'idc' . DIRECTORY_SEPARATOR,
				'docDir' => DIRECTORY_SEPARATOR . 'saa' . DIRECTORY_SEPARATOR,
				'uploadDir' => DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR,
				'tempDir' => DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
		],

		'as beforeRequest' => [
				'class' => 'yii\filters\AccessControl',
				'rules' => [
						[
								'allow' => true,
								'actions' => ['login'],
						],
						[
								'allow' => true,
								'roles' => ['@'],
						],
				],
				'denyCallback' => function () {
					return Yii::$app->response->redirect(['site/login']);
				},
		],		
		
		'catchAll' => file_exists(dirname(__DIR__) . '/.maintenance.on') && !(isset($_COOKIE['secret']) && $_COOKIE['secret'] == "dbosmaint") ? ['/maintenance/index'] : null,
];

if(YII_ENV_DEV) {
	$config['bootstrap'][] = 'debug';
    $config['modules']['gii']['class'] = 'yii\gii\Module';
    $config['modules']['gii']['allowedIPs'] = ['*'];
    $config['modules']['debug']['class'] = 'yii\debug\Module';
    $config['modules']['debug']['allowedIPs'] = ['*'];
}

return $config;