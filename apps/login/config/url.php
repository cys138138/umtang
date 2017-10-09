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
		//新版首页
		''															=> 'site/home',

		'business/home'												=> 'site/index',
		'business/intro'											=> 'site/show-intro',
		'business/faq'												=> 'site/show-fqa',
		'business/prcoess'											=> 'site/show-process',
		'business/about.html'										=> 'site/show-about',

		'site/home.html'											=> 'site/show-home',
		'captcha.html'												=> 'site/captcha',
		
		'login/login.html'											=> 'login/show-login',
		'login/login.json'											=> 'login/login',
		'login/send-login-verify-code.json'							=> 'login/send-login-verify-code',
		'login/mobile-login.json'									=> 'login/mobile-login',
		'login/register.html'										=> 'login/show-register',
		'login/register.json'										=> 'login/register',
		'login/find-password.html'									=> 'login/show-find-password',
		'login/send-find-password-verify-code.json'					=> 'login/send-find-password-verify-code',
		'login/verify-find-password-verify-code.json'				=> 'login/verify-find-password-verify-code',
		'login/logout.html'											=> 'login/logout',
		'login/logout.json'											=> 'login/logout-asynchronous',
		'login/test-login.html'										=> 'login/test-login',
		
		//首页
		'about/company'												=> 'site/about-company',
		'join'														=> 'site/join',
		'join-list.json'											=> 'site/get-join-list',
		'get-join-info.json'										=> 'site/join-one',
		'help/faq'													=> 'site/help-faq',
		'about/contact'												=> 'site/about-contact',
		'about/terms'												=> 'site/about-terms',
		'about/law'													=> 'site/about-law',
		'about/privacy'												=> 'site/about-privacy',
		'my-shop.html'												=> 'site/my-shop',
		'business-settled-apply.json'								=> 'site/business-settled-apply',
		//'umt-about'																=> 'site/umt-about',
		'business/protocol'											=> 'site/business-protocol',
	],
];
