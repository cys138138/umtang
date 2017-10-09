<?php
$params = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/params.php'),
    require(__DIR__ . '/../../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
    //require(__DIR__ . '/params-local.php')
);
return [
    'id' => 'login',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'login\controllers',
    'runtimePath' => PROJECT_PATH . '/runtime/login',
    'components' => [
		'view' => [
			// 'commonTitle' => '优满堂(umtang)',
			'baseTitle' => '优满堂(umtang)',
		],
    ],
	'layout' => 'login',
	'urlManagerName' => 'urlManagerLogin',
//	'catchAll' => [
//        'remind/close-website-remind',
//		'words' => '',
//		'start_time' => 0,
//		'end_time' => 0,
//    ],
    'params' => $params,
];
