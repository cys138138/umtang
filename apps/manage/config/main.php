<?php
$params = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/params.php'),
    require(__DIR__ . '/../../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
    //require(__DIR__ . '/params-local.php')
);
return [
    'id' => 'manage',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'manage\controllers',
    'runtimePath' => PROJECT_PATH . '/runtime/manage',
    'components' => [
		'view' => [
			'commonTitle' => '优满堂(umtang)',
			'baseTitle' => '优满堂(umtang)',
		],
		'manager' => [
			'class' => 'manage\lib\ManagerRole',
			'identityClass' => 'manage\model\Manager',
			'loginUrl' => ['site/show-login'],
			'enableMultipleLogin' => true,
		],
		'authManager' => [
			'class' => 'manage\lib\AuthManager',
			'aPermissionList' => include(__DIR__ . '/permission.php'),
		],
		'ui' => [
			'class' => 'common\ui\CommonUi1',
		],
    ],
	'layout' => 'main',
	'urlManagerName' => 'urlManagerManage',
//	'catchAll' => [
//        'remind/close-website-remind',
//		'words' => '',
//		'start_time' => 0,
//		'end_time' => 0,
//    ],
    'params' => $params,
];
