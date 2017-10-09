<?php
return [
    'vendorPath' => FRAMEWORK_PATH,
    'domain' => 'umtang.' . $aLocal['domain_suffix'][YII_ENV],
    'aWebAppList' => [
		'login', 'tenant', 'api'
	],
    'language' => 'zh-CN',
    'bootstrap' => ['log'],
	'defaultRoute' => 'site/index',
//	'catchAll' => [
//        'remind/close-website-remind',
//		'words' => '',
//		'start_time' => 0,
//		'end_time' => 0,
//    ],
    'components' => [
		//各APP的URL管理器 start
		'urlManagerLogin' => require(Yii::getAlias('@login') . '/config/url.php'),
		'urlManagerManage' => require(Yii::getAlias('@manage') . '/config/url.php'),
		'urlManagerTenant' => require(Yii::getAlias('@tenant') . '/config/url.php'),
		'urlManagerApi' => require(Yii::getAlias('@api') . '/config/url.php'),
		//各APP的URL管理器 end

        'request' => [
            'cookieValidationKey' => 'EArv76QW-Dc8ngUP-qndrD0BDlodbqw-',
        ],

		'ui' => [
			'class' => 'common\ui\CommonUi1',
			'aTips' => [
				'error' => [
					'common' => '抱歉,系统繁忙,请重试',
				],
			],
		],

		'jpush'=>[
			'class' => 'umeworld\lib\Jpush',
			'appKey' => '863fcfa228f5e5eec1cc625c',
			'masterSecret' => 'f728efd3424a124159ca4eb7',
		],
		
		'sms'=>[
			'class' => 'umeworld\lib\Sms',
			'username' => 'Uexiao2016',
			'password' => '1ab4293b0dfbed034bf6',
		],

		'assetManager' => [
			'bundles' => [
				'yii\web\JqueryAsset' => [
					'sourcePath' => null,
					'js' => []
				],
			]
		],

		'response' => [
			'class' => 'yii\web\Response',
			'format' => 'html',
		],

		'notifytion' => [
			'class' => 'common\lib\Notifytion',
		],

        'log' => require(__DIR__ . '/log.php'),

		'errorHandler' => [
			'class' => 'common\lib\ErrorHandler',
			'errorAction' => 'site/error',	//所有站点APP统一使用site控制器的error方法处理网络可能有点慢
		],

		'view' => [
			'class' => 'umeworld\lib\View',
			'on beginPage' => function(){
				Yii::$app->view->title = \yii\helpers\Html::encode(Yii::$app->view->title);

				Yii::$app->view->registerLinkTag([
					'rel' => 'shortcut icon',
					'href' => Yii::getAlias('@r.url') . '/favicon.ico',
				]);

				Yii::$app->view->registerMetaTag([
					'name' => 'csrf-token',
					'content' => Yii::$app->request->csrfToken,
				]);
				
				if(YII_ENV_PROD){
					header("Content-Security-Policy: upgrade-insecure-requests");
				}
			},

			'on endPage' => function(){
				// echo '<!--umfun';	//防止尾部运营商注入广告脚本,IE会显示半截标签，暂时屏蔽
			},
			'on endBody' => function(){
				// echo '<!--umfun';	//防止尾部运营商注入广告脚本,IE会显示半截标签，暂时屏蔽
				echo \common\widgets\Https::widget();
			},
		],

        /*'loginManager' => [
            //'class' => 'umeworld\lib\Redis',
            'class' => 'yii\caching\FileCache',
        ],*/

       'db' => [
            'class' => 'umeworld\lib\Connection',
            'charset' => 'utf8',
			'aTables' => [
				/**
				 * 当你要求user表不使用缓存
				 * 'user' => 'cache:0'
				 *
				 * 当你的某个表不在主库umfun,而是在财务库umfun_recharge
				 * 'recharge' => 'table:umfun_recharge.recharge'		//以recharge为别名指向具体的数据库,必须有table:
				 *
				 * 既定义数据库的具体位置又定义是否缓存
				 * 'recharge' => 'table:db2.recharge;cache:0'	//这里增加了cache控制,1/0表示是否缓存数据,其实语法就像CSS一样
				 *
				 * 以后若有更多控制需求,可以增加"CSS属性"并在 umeworld\lib\Query::from 类里做解析代码
				 */
			],

			'masterConfig' => [
				'username' => $aLocal['db']['master']['username'],
				'password' => $aLocal['db']['master']['password'],
				'attributes' => [
					// use a smaller connection timeout
					PDO::ATTR_TIMEOUT => 10,
					PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
				],
			],

			'masters' => $aLocal['db']['master']['node'],

			'slaveConfig' => [
				'username' => $aLocal['db']['slaver']['username'],
				'password' => $aLocal['db']['master']['password'],
				'attributes' => [
					// use a smaller connection timeout
					PDO::ATTR_TIMEOUT => 10,
					PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
				],
			],

			'slaves' => $aLocal['db']['slaver']['node'],
		],

        'redis' => [
            'class' => 'umeworld\lib\RedisCache',
			'serverName' => $aLocal['cache']['redis']['server_name'],
			'dataPart'	=>	[
				'index'		=>	$aLocal['cache']['redis']['part']['data'],
				'is_active'	=>	0,
			],
			'loginPart' =>	[
				'index'		=>	$aLocal['cache']['redis']['part']['login'],
				'is_active'	=>	0,
			],
			'tempPart'	=>	[
				'index'		=>	$aLocal['cache']['redis']['part']['temp'],
				'is_active'	=>	0,
			],
			'servers' => [
				'redis_1' => [
					'is_active' => 0,
					'host'		=>	$aLocal['cache']['redis']['host'],
					'port'		=>	$aLocal['cache']['redis']['port'],
					'password'	=>	$aLocal['cache']['redis']['password'],
				],
			],
		],

        'redisCache' => [
            'class' => 'umeworld\lib\RedisCache',
			'serverName' => $aLocal['cache']['redisCache']['server_name'],
			'dataPart'	=>	[
				'index'		=>	$aLocal['cache']['redisCache']['part'],
				'is_active'	=>	0,
			],
			'servers' => [
				'redis_1' => [
					'is_active' => 0,
					'host'		=>	$aLocal['cache']['redisCache']['host'],
					'port'		=>	$aLocal['cache']['redisCache']['port'],
					'password'	=>	$aLocal['cache']['redisCache']['password'],
				],
			],
		],

		'client' => [
			'class' => 'umeworld\helper\Client'
		],

		'weiXin' => [
			'class' => 'umeworld\lib\WeiXin',
			'appId' => 'wx0a8cac6ded6588c8',
			'appSecret' => '05dd135ae127c7e74577eb403306e268',
		],
		
		'wxPay' => [
			'class' => 'umeworld\lib\weixin_pay\WxPay',
			'appId' => 'wx2421b1c4370ec43b',
			'mchId'	=>	10000100,
			'key'	=>	'192006250b4c09247ec02edce69f6a2d',
			'mchName'	=>	'优满堂',
			'notifyUrl'	=>	'https://www.umtang.com/weixin/test/test-notify.json',
			'sslcentPath' => Yii::getAlias('@umeworld') . '/lib/weixin_pay/cert/apiclient_cert.pem',
			'sslkeyPath' => Yii::getAlias('@umeworld') . '/lib/weixin_pay/cert/apiclient_key.pem',
		],
		'commercialTenant' => [
			'class' => 'common\role\CommercialTenantRole',
			'identityClass' => 'common\model\CommercialTenant',
			'loginUrl' => ['login/show-login'],
			'enableMultipleLogin' => true,
		],
		'user' => [
			'class' => 'common\role\UserRole',
			'identityClass' => 'common\model\User',
		],
		'authManager' => [
			'class' => 'common\role\AuthManager',
		],
		
		'tencentMap' => [
			'class' => 'umeworld\lib\TencentMap',
			'secretKey' => '52OBZ-UKAKF-EPQJB-NQHNE-SWHI3-DHF6O',
		],
					
		'excel' => [
			'class' => 'umeworld\lib\PHPExcel\excel',
			'schoolId' => '',
			'inputPath' => '',
			'outputPath' => '',
			'type' => '',
			'aType' => ['student', 'teacher'],
			'studentLength'	=> 8,	//学生excel表格的长度
			'teacherLength'	=> 7,	//教师excel表格的长度
		],
    ],
];
