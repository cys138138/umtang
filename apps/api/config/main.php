<?php
$params = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/params.php'),
    require(__DIR__ . '/../../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
    //require(__DIR__ . '/params-local.php')
);
return [
    'id' => 'api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'runtimePath' => PROJECT_PATH . '/runtime/api',
    'components' => [
		'view' => [
			// 'commonTitle' => '优满堂(umtang)',
			'baseTitle' => '优满堂(umtang)',
		],
    ],
	'layout' => 'api',
	'urlManagerName' => 'urlManagerApi',
//	'catchAll' => [
//        'remind/close-website-remind',
//		'words' => '',
//		'start_time' => 0,
//		'end_time' => 0,
//    ],
    'params' => $params,
];
