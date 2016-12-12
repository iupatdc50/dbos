<?php
return [ 
		'id' => 'dbos-console',
		'basePath' => realPath( __DIR__ . '/../'),
		'controllerNamespace' => 'app\commands',
		'components' => [ 
				'db' => require(__DIR__ . '/db.php'),
				'authManager' => require(__DIR__ . '/auth.php'),
		],
];