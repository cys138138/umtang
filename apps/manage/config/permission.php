<?php
//管理权限与菜单配置
return [
	[
		'title' => '审核',
		'icon_class' => 'cube',
		'child' => [
			[
				'title' => '商户初审',
				'url' => ['approve/show-first-approve-list'],
				'icon_class' => 'child',
			],
			[
				'title' => '商户审核',
				'url' => ['approve/show-tenant-approve-list'],
				'icon_class' => 'list',
			],
			[
				'title' => '商铺审核',
				'url' => ['approve/show-tenant-shop-approve-list'],
				'icon_class' => 'star-half-full',
			],
			[
				'title' => '商品审核',
				'url' => ['goods/show-goods-approve-list'],
				'icon_class' => 'trophy',
			],
		],
	],
	[
		'title' => '商户服务',
		'icon_class' => 'delicious',
		'child' => [
			[
				'title' => '默认特色服务',
				'url' => ['tenant-service/show-default-characteristic-service-type-list'],
				'icon_class' => 'list',
			],
			[
				'title' => '商户类型',
				'url' => ['tenant-service/show-commercial-tenant-type-list'],
				'icon_class' => 'list',
			],
		],
	],
	[
		'title' => '订单管理',
		'icon_class' => 'calendar',
		'child' => [
			[
				'title' => '订单流水',
				'url' => ['order/show-home'],
				'icon_class' => 'list',
			],
			[
				'title' => '订单退款',
				'url' => ['order/show-refund-money-list'],
				'icon_class' => 'comments',
			],
		],
	],
	[
		'title' => '账户管理',
		'icon_class' => 'rmb',
		'child' => [
			[
				'title' => '商户提现',
				'url' => ['account/show-withdraw-cash-list'],
				'icon_class' => 'list',
			],
		],
	],
	[
		'title' => '系统功能',
		'icon_class' => 'cube',
		'child' => [
			[
				'title' => '商户公告',
				'url' => ['announcement/show-list'],
				'icon_class' => 'list',
			],
			[
				'title' => '商户列表',
				'url' => ['tenant/show-list'],
				'icon_class' => 'list',
			],
			[
				'title' => '商品列表',
				'url' => ['tenant/show-goods-info-list'],
				'icon_class' => 'list',
			],
		],
	]
];