<?php

$host_env = __DIR__ . '/../config/host-env.php';
if (file_exists($host_env))
	@include $host_env;

require(__DIR__ . '/../vendor/autoload.php');
// Including the Yii framework itself
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// Getting the configuration 
$config = require(__DIR__ . '/../config/web.php');

// Making and launching the application immediately
(new yii\web\Application($config))->run();
