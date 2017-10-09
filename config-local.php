<?php

defined('PROJECT_PATH') || define('PROJECT_PATH', __DIR__);
defined('FRAMEWORK_PATH') || define('FRAMEWORK_PATH', PROJECT_PATH . '/framework'      );

$aLocal = [
	'is_debug' => true,
	'env' => 'local',
	'domain_suffix' => [
		'local' => 'local',
		'dev' => 'dev',
		'test' => 'test',
		'prod' => 'com',
	],
	'db' => [
		'master' => [
			'host' => '192.168.1.202',
			'username' => 'umfun_php_m',
			'password' => '121212',
			'node' => [
				['dsn' => 'mysql:host=192.168.1.202;dbname=umtang'],
			],
		],
		'slaver' => [
			'host' => '192.168.1.202',
			'username' => 'umfun_php_s',
			'password' => '121212',
			'node' => [
				['dsn' => 'mysql:host=192.168.1.202;dbname=umtang'],
			],
		],
	],
	'cache' => [
		'redis' => [
			'host'		=>	'192.168.1.202',
			'port'		=>	'6379',
			'password'	=>	'',
			'server_name' => 'redis_1',
			'part' => [
				'data' => 5,
				'login' => 6,
				'temp' => 7,
			],
		],

		'redisCache' => [
			'host'		=>	'192.168.1.202',
			'port'		=>	'6379',
			'password'	=>	'',
			'server_name' => 'redis_1',
			'part' => 7,
		],
	],
	'temp' => [],
];


if(isset($_SERVER['SERVER_ADDR'])){
	if($_SERVER['SERVER_ADDR'] == '192.168.1.202'){
		$aLocal['env'] = 'test';
		$aLocal['cache']['redis']['part']['login'] = 4;
	}elseif($_SERVER['SERVER_ADDR'] == '115.159.155.71'){
		$aLocal['env'] = 'prod';
	}
}

if(!class_exists('Yii')){
	defined('YII_DEBUG') || define('YII_DEBUG', $aLocal['is_debug']);
	defined('YII_ENV') || define('YII_ENV', $aLocal['env']);
	require(FRAMEWORK_PATH . '/autoload.php');
	require(FRAMEWORK_PATH . '/yiisoft/yii2/Yii.php');
	require(PROJECT_PATH . '/common/config/bootstrap.php');
}