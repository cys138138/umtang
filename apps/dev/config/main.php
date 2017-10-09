<?php
return [
	'id' => 'dev',
	'basePath' => dirname(__DIR__),
	'modules' => [
		'debug' => 'yii\debug\Module',
		'gii' => 'yii\gii\Module',
	],
	'controllerNamespace' => 'dev\controllers',
	'runtimePath' => PROJECT_PATH . '/runtime/dev',
	'components' => [
		'db_dev' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=192.168.1.202;dbname=umfun_dev',
            'username' => 'umfun_php_m',
            'password' => '121212',
            'charset' => 'utf8',
		],
		'view' => [
			'commonTitle' => '开发辅助工具',
			'baseTitle' => '开发辅助工具',
		],
		'urlManager' => [
			'enablePrettyUrl' => false,
			'showScriptName' => false,
			'enableStrictParsing' => false,
		],
		'log' => [
			'targets' => [
				[
				'class' => 'umeworld\lib\FileLogTarget',
				'categories' => ['answer_over'],
				'levels' => ['info'],
				'logVars' => [],
				'logFile' => '@runtime/logs/answer' . date('m-d') . '.log',
				],
			],
		],
	],	
//	'catchAll' => [
//        'remind/close-website-remind',
//		'words' => '',
//		'start_time' => 0,
//		'end_time' => 0,
//    ],
	'urlManagerName' => 'urlManager',
	'params' => yii\helpers\ArrayHelper::merge(
		require(__DIR__ . '/../../../common/config/params.php'),
		require(__DIR__ . '/../../../common/config/params-local.php'),
		require(__DIR__ . '/params.php'),
		require(__DIR__ . '/params-local.php')
	),
];
