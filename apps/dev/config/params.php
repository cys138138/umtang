<?php
return [
	'last_svn_build_log_count' => 5,	//获取最后5次svn构建历史
	'db_diff' => [
		//以下配置仅作演示
		'db-a' => [
			//这里是第一个数据库的信息,格式跟yii配置一样
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=地址1;dbname=数据库a',
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
		],
		'db-b' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=地址2;dbname=数据库b',
			'username' => 'root',
			'password' => '123456',
			'charset' => 'utf8',
		],
	],
];