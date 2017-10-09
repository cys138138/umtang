<?php
$aLocal['temp']['todayLog'] = date('m-d') . '.log';	//今天的日志名称
return [
	'traceLevel' => YII_DEBUG ? 10 : 5,
	'targets' => [
		[
			//普通消息
			'class' => 'umeworld\lib\FileLogTarget',
			'levels' => ['info'],
			'categories' => ['application'],
			'logFile' => '@runtime/logs/info_' . $aLocal['temp']['todayLog'],
		],
		[
			//跨站POST请求攻击
			'class' => 'umeworld\lib\FileLogTarget',
			'levels' => ['error'],
			'categories' => ['yii\web\HttpException:400'],
			'logFile' => '@runtime/logs/400_' . $aLocal['temp']['todayLog'],
		],
		/*'page_not_found' => [
			//404页面
			'class' => 'umeworld\lib\FileLogTarget',
			'levels' => ['error'],
			'categories' => [
				'yii\web\HttpException:404',
				'yii\web\NotFoundHttpException',
			],
			'logFile' => '@runtime/logs/404_' . $aLocal['temp']['todayLog'],
		],*/

		'all_error' => [
			//全部警告和错误
			'class' => 'umeworld\lib\FileLogTarget',
			'levels' => ['warning', 'error'],
			'logFile' => '@runtime/logs/error_' . $aLocal['temp']['todayLog'],
			'aFilterCategorys' => [
				//过滤掉的分类,如果不过滤则会把上面定义的错误都记下
				'yii\web\HttpException:400',
				'yii\web\BadRequestHttpException',
				'yii\web\HttpException:404',
				'yii\web\NotFoundHttpException',
				'yii\web\HttpException:511',
				'yii\web\HttpException:514',
				'common\xxt\*',
				'yii\db\IntegrityException:23000',
			]
		],
		/*[
			'class' => 'yii\log\DbTarget',
			'levels' => ['error', 'warning'],
		],*/
	],
];