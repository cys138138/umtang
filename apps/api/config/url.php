<?php
/**
 * URL配置控制
 * class : 解析器
 * enablePrettyUrl : 是否开启伪静态
 * showScriptName : 生成的URL是否带入口脚本名称
 * enableStrictParsing : 是否开启严格匹配
 * baseUrl 域名
 */
return [
	'class' => 'yii\web\UrlManager',
	'enablePrettyUrl' => true,
	'showScriptName' => false,
	'enableStrictParsing' => true,
	'baseUrl' => Yii::getAlias('@url.base'),
	'rules' => [
		// ''															=> 'site/index',
		
		'api/login.json'												=> 'login/login',
		
		
		'api/tenant-type-list.json'										=> 'index/get-tenant-type-list',
		'api/nearly-tenant-list.json'									=> 'index/get-nearly-tenant-list',
		'api/guess-you-like-list.json'									=> 'index/get-guess-you-like-list',
		'api/get-goods-list-by-ids.json'								=> 'index/get-goods-list-by-ids',
		'api/get-tenant-list.json'										=> 'index/get-tenant-list',
		'api/get-user-locate-city.json'									=> 'index/get-user-locate-city',
		'api/get-other-city-list.json'									=> 'index/get-other-city-list',
		'api/search-tenant-or-goods.json'								=> 'index/search-tenant-or-goods',
		'api/teacher-list.json'											=> 'index/get-teacher-list',
		'api/get-teacher.json'											=> 'index/get-teacher',
		
		
		'api/user-info.json'											=> 'user/get-user-info',
		'api/user-notice-list.json'										=> 'user/get-user-notice-list',
		'api/user-accumulate-points-get-list.json'						=> 'user/get-user-accumulate-points-get-list',
		'api/user-accumulate-points-use-list.json'						=> 'user/get-user-accumulate-points-use-list',
		'api/user-collect-list.json'									=> 'user/get-user-collect-list',
		'api/user-comment-list.json'									=> 'user/get-user-comment-list',
		'api/user-task-list.json'										=> 'user/get-user-task-list',
		'api/user-task-prize.json'										=> 'user/get-user-task-prize',
		'api/user-collect.json'											=> 'user/user-collect',
		'api/user-collect-cancel.json'									=> 'user/user-collect-cancel',
		'api/bind-user-info.json'										=> 'user/bind-user-info',
		'api/check-unread-message-status.json'							=> 'user/check-unread-message-status',
		'api/user-update-info.json'										=> 'user/update-info',
		
		//商品&下单&买单
		'api/goods/get-home-data.json'									=> 'goods/get-home-data',
		'api/goods/get-goods-list.json'									=> 'goods/get-goods-list',
		'api/goods/get-teacher-list.json'								=> 'goods/get-teacher-list',
		'api/goods/get-comment-list.json'								=> 'goods/get-comment-list',
		'api/goods/get-goods-details.json'								=> 'goods/get-goods-details',
		'api/goods/start-pay-order.json'								=> 'goods/pay-order',
		'api/goods/order-pay-result.json'								=> 'goods/order-pay-result',
		'api/goods/start-direct-pay.json'								=> 'goods/direct-pay',
		'api/goods/direct-pay-result.json'								=> 'goods/direct-pay-result',
		'api/goods/notify-after-weixin-pay.json'						=> 'goods/notify-after-weixin-pay',
		'api/goods/upload-file.json'									=> 'goods/upload-file',
		'api/goods/before-pay-info.json'								=> 'goods/before-pay-info',
		'api/goods/get-mobile-code.json'								=> 'goods/get-mobile-code',
		'api/goods/bind-mobile.json'									=> 'goods/bind-mobile',
		'api/goods/get-banner-list.json'								=> 'goods/get-banner-list',
		
		//订单
		'api/order/get-home-data.json'									=> 'order/get-home-data',
		'api/order/get-order-data.json'									=> 'order/get-order-list',
		'api/order/get-order-details.json'								=> 'order/get-order-details',
		'api/order/comment-order.json'									=> 'order/comment-order',
		'api/order/apply-refund.json'									=> 'order/apply-refund',
		'api/order/cancel-refund.json'									=> 'order/cancel-refund',
		'api/order/superaddition-comment-order.json'					=> 'order/superaddition-comment',
	],
];