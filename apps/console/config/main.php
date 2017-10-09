<?php
$params = array_merge(
    require(Yii::getAlias('@common/config/params.php')),
    require(Yii::getAlias('@common/config/params-local.php')),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'runtimePath' => PROJECT_PATH . '/runtime/console',
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

       'db' => [
            'class' => 'umeworld\lib\Connection',
            'charset' => 'utf8',
			'aTables' => [
				'recharge' => 'umfun_recharge.recharge',
			],

			'masterConfig' => [
				'username' => $aLocal['db']['master']['username'],
				'password' => $aLocal['db']['master']['password'],
				'attributes' => [
					// use a smaller connection timeout
					PDO::ATTR_TIMEOUT => 10,
				],
			],

			'masters' => $aLocal['db']['master']['node'],

			'slaveConfig' => [
				'username' => $aLocal['db']['slaver']['username'],
				'password' => $aLocal['db']['master']['password'],
				'attributes' => [
					// use a smaller connection timeout
					PDO::ATTR_TIMEOUT => 10,
				],
			],

			'slaves' => $aLocal['db']['slaver']['node'],
		],

        'redis' => [
            'class' => 'umeworld\lib\RedisCache',
			'serverName' => $aLocal['cache']['redis']['server_name'],
			'dataPart'	=>	[
				'index'		=>	$aLocal['cache']['redis']['part']['data'],
				'is_active'	=>	1,
			],
			'loginPart' =>	[
				'index'		=>	$aLocal['cache']['redis']['part']['login'],
				'is_active'	=>	1,
			],
			'tempPart'	=>	[
				'index'		=>	$aLocal['cache']['redis']['part']['temp'],
				'is_active'	=>	1,
			],
			'servers' => [
				'redis_1' => [
					'is_active' => 1,
					'host'		=>	$aLocal['cache']['redis']['host'],
					'port'		=>	$aLocal['cache']['redis']['port'],
					'password'	=>	$aLocal['cache']['redis']['password'],
				],
			],
		],

		'os' => [
			'class' => 'umeworld\helper\System'
		],
    ],
    'params' => $params,
];
