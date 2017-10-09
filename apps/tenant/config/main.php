<?php
$params = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/params.php'),
    require(__DIR__ . '/../../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
    //require(__DIR__ . '/params-local.php')
);
return [
    'id' => 'tenant',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'tenant\controllers',
    'runtimePath' => PROJECT_PATH . '/runtime/tenant',
    'components' => [
		'view' => [
			// 'commonTitle' => '优满堂(umtang)',
			'baseTitle' => '优满堂(umtang)',
		],
    ],
	'layout' => 'tenant',
	'urlManagerName' => 'urlManagerTenant',
//	'catchAll' => [
//        'remind/close-website-remind',
//		'words' => '',
//		'start_time' => 0,
//		'end_time' => 0,
//    ],
    'params' => $params,
];
