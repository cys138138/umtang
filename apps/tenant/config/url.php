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
		'tenant'																=> 'site/show-index',
		'tenant/index.html'														=> 'site/show-index',
		
		'tenant/fill-approve.html'												=> 'tenant/show-fill-approve',
		'tenant/save-fill-approve.json'											=> 'tenant/save-fill-approve',
		'tenant/upload-photo.json'												=> 'tenant/upload-photo',
		'tenant/approve-status.html'											=> 'tenant/show-approve-status',
		'tenant/reset-password.json'											=> 'tenant/reset-password',
		'tenant/approve-info.html'												=> 'tenant/show-approve-info',
		'tenant/save-approve-info.json'											=> 'tenant/save-approve-info',
		'tenant/send-mobile-verify-code.json'									=> 'tenant/send-mobile-verify-code',
		'tenant/bind-mobile.json'												=> 'tenant/bind-mobile',
		'tenant/discount-info.html'												=> 'tenant/show-discount-info',
		'tenant/save-discount-info.json'										=> 'tenant/save-discount-info',
		
		'tenant/tenant-shop/fill-tenant-shop.html'								=> 'tenant-shop/show-fill-tenant-shop',
		'tenant/tenant-shop/save-fill-tenant-shop.json'							=> 'tenant-shop/save-fill-tenant-shop',
		'tenant/tenant-shop/shop-info.html'										=> 'tenant-shop/show-shop-info',
		'tenant/tenant-shop/save-shop-info.json'								=> 'tenant-shop/save-shop-info',
		'tenant/tenant-shop/upload-profile.json'								=> 'tenant-shop/upload-profile',
		
		
		'tenant/announcement/list.json'											=> 'announcement/get-list',
		'tenant/announcement/detail/<id:\w+>.html'								=> 'announcement/show-detail',
		
		
		'tenant/notice/index.html'												=> 'notice/index',
		'tenant/notice/list.json'												=> 'notice/get-list',
		'tenant/notice/set-read.json'											=> 'notice/set-read',
		
		
		'tenant/photo/index.html'												=> 'photo/index',
		'tenant/photo/list.json'												=> 'photo/get-list',
		'tenant/photo/upload.json'												=> 'photo/upload',
		'tenant/photo/set-cover.json'											=> 'photo/set-cover',
		'tenant/photo/delete.json'												=> 'photo/delete',
		
		
		'tenant/characteristic/index.html'										=> 'characteristic/index',
		'tenant/characteristic/save-setting.json'								=> 'characteristic/save-setting',
		'tenant/characteristic/add.json'										=> 'characteristic/add',
		
		
		'tenant/teacher/index.html'												=> 'teacher/index',
		'tenant/teacher/edit/<id:\w+>-<createTime:\w+>.html'										=> 'teacher/show-edit',
		'tenant/teacher/save.json'												=> 'teacher/save',
		'tenant/teacher/upload-profile.json'									=> 'teacher/upload-profile',
		'tenant/teacher/set-order.json'											=> 'teacher/set-order',
		'tenant/teacher/delete.json'											=> 'teacher/delete',
		
		
		//商品
		'tenant/goods/show-home.html'											=> 'goods/show-home',
		'tenant/goods/get-goods-data.json'										=> 'goods/get-goods-list',
		'tenant/goods/show-add-goods.html'										=> 'goods/show-add-goods',
		'tenant/goods/submit-new-goods.json'									=> 'goods/add-goods',
		'tenant/goods/show-edit-goods/<goods_id:\w+>.html'						=> 'goods/show-edit-goods',
		'tenant/goods/submit-edit-goods.json'									=> 'goods/edit-goods',
		'tenant/goods/show-goods-photo/<goods_id:\w+>.html'						=> 'goods/show-goods-photo',
		'tenant/goods/get-goods-photo.json'										=> 'goods/get-goods-photo-list',
		'tenant/goods/operate-goods.json'										=> 'goods/operate-goods',
		'tenant/goods/operate-photo.json'										=> 'goods/operate-photo',
		'tenant/goods/add-photo.json'											=> 'goods/add-photo',
		'tenant/goods/upload-file.json'											=> 'goods/upload-file',
		
		//订单
		'tenant/order/show-home.html'											=> 'order/show-home',
		'tenant/order/get-order-data.json'										=> 'order/get-order-list',
		
		//商品卷
		'tenant/goods-volume/show-home.html'									=> 'goods-volume/show-home',
		'tenant/goods-volume/get-activate-info.json'							=> 'goods-volume/get-activate-info',
		'tenant/goods-volume/activate.json'										=> 'goods-volume/activate',
		
		//资金池
		'tenant/fund/show-home.html'											=> 'fund/show-home',
		'tenant/fund/get-mobile-code.json'										=> 'fund/get-mobile-code',
		'tenant/fund/extract-money.json'										=> 'fund/extract-money',
		'tenant/fund/show-order.html'											=> 'fund/show-order',
		'tenant/fund/get-order-data.json'										=> 'fund/get-order-list',
		'tenant/fund/show-extract.html'											=> 'fund/show-extract',
		'tenant/fund/get-extract-data.json'										=> 'fund/get-extract-list',
		
		//订单评价
		'tenant/comment/show-home.html'											=> 'comment/show-home',
		'tenant/comment/get-comment-data.json'									=> 'comment/get-comment-list',
		'tenant/comment/show-details/<order_id:\w+>.html'						=> 'comment/comment-details',
		'tenant/comment/reply-comment.json'										=> 'comment/reply-comment',
	],
];