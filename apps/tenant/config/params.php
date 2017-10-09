<?php
return [
	//商铺各项信息每月修改次数信息
	'tenant_shop_modify_limit_count' => [
		'profile' => 3,
		'name' => 1,
		'address' => 1,
		'email' => 3,
		'bank_accout' => 1,
		'description' => 5,
		'contact_number' => 5,
		'commercial_type' => 5,
		'leading_official' => 5,
		'identity_card' => 5,
		'mobile' => 5,
		'bank_account_holder' => 5,
		'bank_name' => 5,
	],
	
	'ui' => require(__DIR__ . '/ui.php'),//广告
];
